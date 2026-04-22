<?php

namespace App\Http\Controllers\Boutique;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Boutique\CreateBoutiqueOrderRequest;
use App\Http\Requests\Boutique\ShippingQuoteRequest;
use App\Models\Boutique\BoutiqueCart;
use App\Models\Boutique\BoutiqueOrder;
use App\Models\Boutique\BoutiqueOrderItem;
use App\Models\Boutique\BoutiquePayment;
use App\Models\Boutique\BoutiqueShipment;
use App\Models\Dealership;
use App\Services\Boutique\BoutiqueInventoryService;
use App\Services\Boutique\EnviacomService;
use App\Services\Boutique\OpenPayService;
use App\Services\Boutique\StripeService;
use App\Support\BoutiqueCheckoutPaymentMethods;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BoutiqueCheckoutController extends Controller
{
    protected BoutiqueInventoryService $inventoryService;
    protected EnviacomService $enviacomService;
    protected StripeService $stripeService;

    protected OpenPayService $openPayService;

    public function __construct(
        BoutiqueInventoryService $inventoryService,
        EnviacomService $enviacomService,
        StripeService $stripeService,
        OpenPayService $openPayService
    ) {
        $this->inventoryService = $inventoryService;
        $this->enviacomService = $enviacomService;
        $this->stripeService = $stripeService;
        $this->openPayService = $openPayService;
    }

    public function shippingQuote(ShippingQuoteRequest $request)
    {
        try {
            $data = $request->validated();

            $destination = [
                'name' => 'Cliente',
                'street' => (string) ($data['shipping_address'] ?? ''),
                'city' => $data['city'],
                'state' => $data['state'],
                'zip' => $data['zip_code'],
                'country' => 'MX',
            ];

            $packages = [
                [
                    'content' => 'Productos Boutique VECSA',
                    'amount' => 1,
                    'type' => 'box',
                    'weight' => 1,
                    'insurance' => 0,
                    'declaredValue' => 0,
                    'weightUnit' => 'KG',
                    'lengthUnit' => 'CM',
                    'dimensions' => [
                        'length' => 30,
                        'width' => 30,
                        'height' => 20,
                    ],
                ],
            ];

            $quotes = [];

            $useEnviacom = filter_var(env('BOUTIQUE_SHIPPING_USE_ENVIACOM', false), FILTER_VALIDATE_BOOLEAN)
                && trim((string) env('ENVIACOM_API_KEY', '')) !== '';

            if ($useEnviacom) {
                try {
                    $raw = $this->enviacomService->getShippingQuotes($destination, $packages);
                    $quotes = is_array($raw) ? $raw : [];
                } catch (\Throwable $e) {
                    Log::warning('Boutique shippingQuote: Enviacom falló, se usa tarifa automática', [
                        'error' => $e->getMessage(),
                    ]);
                    $quotes = [];
                }
            }

            if ($quotes === []) {
                $quotes = $this->boutiqueAutomaticShippingQuotes();
            }

            return ApiResponseHelper::apiSuccess(200, 'Cotizaciones de envío obtenidas', ['shipping_options' => $quotes]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener cotizaciones de envío', $e->getMessage(), 500, 'SHIPPING_QUOTE_ERROR');
        }
    }

    /**
     * Cotización local cuando Enviacom no está activo o falla (p. ej. sandbox sin ENVIACOM_API_KEY).
     */
    private function boutiqueAutomaticShippingQuotes(): array
    {
        $flat = (float) env('BOUTIQUE_SHIPPING_FALLBACK_FLAT_MXN', 99);
        $days = (int) env('BOUTIQUE_SHIPPING_FALLBACK_DAYS', 5);

        return [[
            'carrier' => 'Tienda',
            'service' => 'Envío estándar',
            'price' => round(max(0, $flat), 2),
            'estimated_days' => max(1, $days),
            'package_type_id' => 'default',
        ]];
    }

    public function createGuestOrder(Request $request)
    {
        try {
            $data = $request->validate([
                'guest_name'       => 'required|string|max:255',
                'guest_email'      => 'required|email|max:255',
                'delivery_method'  => 'required|in:envio_domicilio,recoleccion_sucursal',
                'payment_method'   => 'required|in:stripe,transferencia,sucursal,openpay',
                'shipping_name'    => 'nullable|string|max:255',
                'shipping_address' => 'nullable|string|max:500',
                'shipping_city'    => 'nullable|string|max:100',
                'shipping_state'   => 'nullable|string|max:100',
                'shipping_zip'     => 'nullable|string|max:10',
                'shipping_phone'   => 'nullable|string|max:20',
                'dealership_uuid'  => 'nullable|string',
                'shipping_option'  => 'nullable|array',
                'notes'            => 'nullable|string',
                'items'            => 'required|array|min:1',
                'items.*.product_uuid' => 'required|string',
                'items.*.quantity'     => 'required|integer|min:1',
                'items.*.variant_uuid' => 'nullable|string',
            ]);

            // Resolve products from UUIDs
            $productUuids = collect($data['items'])->pluck('product_uuid')->unique()->toArray();
            $products = \App\Models\Boutique\BoutiqueProduct::whereIn('uuid', $productUuids)->get()->keyBy('uuid');

            // Verify stock
            $insufficientStock = [];
            foreach ($data['items'] as $item) {
                $product = $products[$item['product_uuid']] ?? null;
                if (!$product) {
                    return ApiResponseHelper::apiError("Producto no encontrado: {$item['product_uuid']}", null, 404, 'PRODUCT_NOT_FOUND');
                }
                if ($product->stock < $item['quantity']) {
                    $insufficientStock[] = [
                        'product' => $product->name,
                        'available' => $product->stock,
                        'requested' => $item['quantity'],
                    ];
                }
            }

            if (!empty($insufficientStock)) {
                return ApiResponseHelper::apiError(
                    'Stock insuficiente para algunos productos',
                    json_encode($insufficientStock),
                    400,
                    'INSUFFICIENT_STOCK',
                    ['items' => $insufficientStock]
                );
            }

            if (! BoutiqueCheckoutPaymentMethods::isMethodEnabled($data['payment_method'])) {
                return ApiResponseHelper::apiError('El método de pago seleccionado no está habilitado', null, 422, 'PAYMENT_METHOD_DISABLED');
            }

            // Calculate totals
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $subtotal += $item['quantity'] * $products[$item['product_uuid']]->price;
            }

            $shippingCost = 0;
            if ($data['delivery_method'] === 'envio_domicilio' && !empty($data['shipping_option'])) {
                $shippingCost = $data['shipping_option']['price'] ?? 0;
            }

            $total = $subtotal + $shippingCost;

            $today = Carbon::now()->format('Ymd');
            $dailyCount = BoutiqueOrder::whereDate('created_at', Carbon::today())->count() + 1;
            $orderNumber = 'BOUT-' . $today . '-' . str_pad($dailyCount, 4, '0', STR_PAD_LEFT);

            $order = DB::transaction(function () use ($data, $products, $subtotal, $shippingCost, $total, $orderNumber) {
                $order = BoutiqueOrder::create([
                    'user_id'          => null,
                    'guest_name'       => $data['guest_name'],
                    'guest_email'      => $data['guest_email'],
                    'order_number'     => $orderNumber,
                    'status'           => 'pendiente',
                    'subtotal'         => $subtotal,
                    'shipping_cost'    => $shippingCost,
                    'total'            => $total,
                    'delivery_method'  => $data['delivery_method'],
                    'shipping_name'    => $data['shipping_name'] ?? null,
                    'shipping_address' => $data['shipping_address'] ?? null,
                    'shipping_city'    => $data['shipping_city'] ?? null,
                    'shipping_state'   => $data['shipping_state'] ?? null,
                    'shipping_zip'     => $data['shipping_zip'] ?? null,
                    'shipping_phone'   => $data['shipping_phone'] ?? null,
                    'notes'            => $data['notes'] ?? null,
                ]);

                foreach ($data['items'] as $item) {
                    $product = $products[$item['product_uuid']];
                    BoutiqueOrderItem::create([
                        'order_id'     => $order->id,
                        'product_id'   => $product->id,
                        'product_name' => $product->name,
                        'product_sku'  => $product->sku,
                        'quantity'     => $item['quantity'],
                        'unit_price'   => $product->price,
                        'subtotal'     => round($item['quantity'] * $product->price, 2),
                    ]);

                    $this->inventoryService->reduceStock($product, $item['quantity'], 'venta', $order->uuid);
                }

                BoutiquePayment::create([
                    'order_id' => $order->id,
                    'method'   => $data['payment_method'],
                    'amount'   => $total,
                    'status'   => 'pendiente',
                ]);

                $shipmentData = [
                    'order_id'        => $order->id,
                    'delivery_method' => $data['delivery_method'],
                    'status'          => 'pendiente',
                ];

                if ($data['delivery_method'] === 'recoleccion_sucursal' && !empty($data['dealership_uuid'])) {
                    $dealership = Dealership::where('uuid', $data['dealership_uuid'])->first();
                    if ($dealership) {
                        $shipmentData['dealership_id'] = $dealership->id;
                    }
                }

                if ($data['delivery_method'] === 'envio_domicilio' && !empty($data['shipping_option'])) {
                    $shipmentData['carrier_name'] = $data['shipping_option']['carrier'] ?? null;
                    $shipmentData['estimated_delivery'] = isset($data['shipping_option']['estimated_days'])
                        ? Carbon::now()->addDays($data['shipping_option']['estimated_days'])->toDateString()
                        : null;
                }

                BoutiqueShipment::create($shipmentData);

                return $order;
            });

            $order->load(['orderItems', 'payment', 'shipment']);

            return ApiResponseHelper::apiSuccess(201, 'Pedido creado exitosamente', ['order' => $order]);
        } catch (ValidationException $e) {
            return ApiResponseHelper::validationError($e);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al crear el pedido', $e->getMessage(), 500, 'CREATE_ORDER_ERROR');
        }
    }

    public function createOrder(CreateBoutiqueOrderRequest $request)
    {
        try {
            $data = $request->validated();
            $user = $request->user();

            // Get user's cart with items
            $cart = BoutiqueCart::where('user_id', $user->id)
                ->with(['items.product'])
                ->first();

            if (!$cart || $cart->items->isEmpty()) {
                return ApiResponseHelper::apiError('El carrito está vacío', null, 400, 'EMPTY_CART');
            }

            // Verify stock for all items
            $insufficientStock = [];
            foreach ($cart->items as $item) {
                if ($item->product->stock < $item->quantity) {
                    $insufficientStock[] = [
                        'product' => $item->product->name,
                        'available' => $item->product->stock,
                        'requested' => $item->quantity,
                    ];
                }
            }

            if (!empty($insufficientStock)) {
                return ApiResponseHelper::apiError(
                    'Stock insuficiente para algunos productos',
                    json_encode($insufficientStock),
                    400,
                    'INSUFFICIENT_STOCK',
                    ['items' => $insufficientStock]
                );
            }

            if (! BoutiqueCheckoutPaymentMethods::isMethodEnabled($data['payment_method'])) {
                return ApiResponseHelper::apiError('El método de pago seleccionado no está habilitado', null, 422, 'PAYMENT_METHOD_DISABLED');
            }

            // Calculate totals
            $subtotal = 0;
            foreach ($cart->items as $item) {
                $subtotal += $item->quantity * $item->product->price;
            }

            $shippingCost = 0;
            if ($data['delivery_method'] === 'envio_domicilio' && !empty($data['shipping_option'])) {
                $shippingCost = $data['shipping_option']['price'] ?? 0;
            }

            $total = $subtotal + $shippingCost;

            // Generate order number: BOUT-YYYYMMDD-XXXX
            $today = Carbon::now()->format('Ymd');
            $dailyCount = BoutiqueOrder::whereDate('created_at', Carbon::today())->count() + 1;
            $orderNumber = 'BOUT-' . $today . '-' . str_pad($dailyCount, 4, '0', STR_PAD_LEFT);

            $order = DB::transaction(function () use ($data, $user, $cart, $subtotal, $shippingCost, $total, $orderNumber) {
                // Create Order
                $order = BoutiqueOrder::create([
                    'user_id' => $user->id,
                    'order_number' => $orderNumber,
                    'status' => 'pendiente',
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shippingCost,
                    'total' => $total,
                    'delivery_method' => $data['delivery_method'],
                    'shipping_name' => $data['shipping_name'] ?? null,
                    'shipping_address' => $data['shipping_address'] ?? null,
                    'shipping_city' => $data['shipping_city'] ?? null,
                    'shipping_state' => $data['shipping_state'] ?? null,
                    'shipping_zip' => $data['shipping_zip'] ?? null,
                    'shipping_phone' => $data['shipping_phone'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ]);

                // Create OrderItems (snapshot product data)
                foreach ($cart->items as $item) {
                    BoutiqueOrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item->product->id,
                        'product_name' => $item->product->name,
                        'product_sku' => $item->product->sku,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->product->price,
                        'subtotal' => round($item->quantity * $item->product->price, 2),
                    ]);

                    // Reduce stock
                    $this->inventoryService->reduceStock(
                        $item->product,
                        $item->quantity,
                        'venta',
                        $order->uuid
                    );
                }

                // Create Payment
                BoutiquePayment::create([
                    'order_id' => $order->id,
                    'method' => $data['payment_method'],
                    'amount' => $total,
                    'status' => 'pendiente',
                ]);

                // Create Shipment
                $shipmentData = [
                    'order_id' => $order->id,
                    'delivery_method' => $data['delivery_method'],
                    'status' => 'pendiente',
                ];

                if ($data['delivery_method'] === 'recoleccion_sucursal' && !empty($data['dealership_uuid'])) {
                    $dealership = Dealership::where('uuid', $data['dealership_uuid'])->first();
                    if ($dealership) {
                        $shipmentData['dealership_id'] = $dealership->id;
                    }
                }

                if ($data['delivery_method'] === 'envio_domicilio' && !empty($data['shipping_option'])) {
                    $shipmentData['carrier_name'] = $data['shipping_option']['carrier'] ?? null;
                    $shipmentData['estimated_delivery'] = isset($data['shipping_option']['estimated_days'])
                        ? Carbon::now()->addDays($data['shipping_option']['estimated_days'])->toDateString()
                        : null;
                }

                BoutiqueShipment::create($shipmentData);

                // Soft delete cart items
                foreach ($cart->items as $item) {
                    $item->delete();
                }

                return $order;
            });

            $order->load(['orderItems', 'payment', 'shipment']);

            return ApiResponseHelper::apiSuccess(201, 'Pedido creado exitosamente', ['order' => $order]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al crear el pedido', $e->getMessage(), 500, 'CREATE_ORDER_ERROR');
        }
    }

    public function createPaymentIntent(Request $request)
    {
        try {
            $orderUuid = $request->input('order_uuid');

            $order = BoutiqueOrder::findByUuid($orderUuid);
            if (!$order) {
                return ApiResponseHelper::apiError('El pedido no existe', null, 404, 'ORDER_NOT_FOUND');
            }

            $result = $this->stripeService->createPaymentIntent(
                (float) $order->total,
                'mxn',
                ['order_uuid' => $order->uuid, 'order_number' => $order->order_number]
            );

            // Store payment intent ID
            $payment = $order->payment;
            if ($payment) {
                $payment->update(['stripe_payment_intent_id' => $result['payment_intent_id']]);
            }

            return ApiResponseHelper::apiSuccess(200, 'PaymentIntent creado exitosamente', [
                'client_secret' => $result['client_secret'],
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al crear el PaymentIntent', $e->getMessage(), 500, 'PAYMENT_INTENT_ERROR');
        }
    }

    /**
     * Confirma el cobro OpenPay con token (OpenPay.js) + device_session_id.
     * Ruta pública para permitir checkout de invitados.
     *
     * TODO (fase posterior): webhook OpenPay para notificaciones asíncronas (p. ej. verificación
     * de cargos, estados distintos de completed en la misma respuesta, reconciliación).
     * Por ahora el flujo queda cerrado con la respuesta síncrona del API de cargos.
     */
    public function confirmOpenPayCharge(Request $request)
    {
        try {
            $data = $request->validate([
                'order_uuid' => 'required|string|max:64',
                'source_id' => 'required|string|max:45',
                'device_session_id' => 'required|string|size:32',
            ]);

            $order = BoutiqueOrder::findByUuid($data['order_uuid']);
            if (! $order) {
                return ApiResponseHelper::apiError('El pedido no existe', null, 404, 'ORDER_NOT_FOUND');
            }

            $payment = $order->payment;
            if (! $payment || $payment->method !== 'openpay') {
                return ApiResponseHelper::apiError('Este pedido no usa OpenPay', null, 400, 'OPENPAY_NOT_APPLICABLE');
            }

            if ($payment->status === 'completado') {
                return ApiResponseHelper::apiError('El pago ya fue registrado', null, 409, 'PAYMENT_ALREADY_COMPLETED');
            }

            $creds = $this->openPayService->getActiveCredentials();
            if ($creds['merchant_id'] === '' || $creds['private_key'] === '') {
                return ApiResponseHelper::apiError('OpenPay no está configurado en el servidor', null, 503, 'OPENPAY_NOT_CONFIGURED');
            }

            $fullName = trim((string) ($order->shipping_name ?: $order->guest_name ?: 'Cliente'));
            $nameParts = preg_split('/\s+/', $fullName, 2) ?: ['Cliente', ''];
            $firstName = $nameParts[0] !== '' ? $nameParts[0] : 'Cliente';
            $lastName = isset($nameParts[1]) && $nameParts[1] !== '' ? $nameParts[1] : $firstName;

            $email = trim((string) ($order->guest_email ?: ''));
            if ($email === '' && $order->user_id) {
                $order->loadMissing('user');
                $email = trim((string) ($order->user?->email ?? ''));
            }
            if ($email === '') {
                $email = 'no-email@boutique.local';
            }

            $phone = preg_replace('/\D+/', '', (string) ($order->shipping_phone ?? ''));
            if (strlen($phone) > 10) {
                $phone = substr($phone, -10);
            }
            if ($phone === '') {
                $phone = '5555555555';
            }

            $customer = [
                'name' => mb_substr($firstName, 0, 100),
                'last_name' => mb_substr($lastName, 0, 100),
                'phone_number' => $phone,
                'email' => mb_substr($email, 0, 100),
            ];

            $amount = (float) $order->total;
            $charge = $this->openPayService->createMerchantCardCharge(
                $creds['merchant_id'],
                $creds['private_key'],
                $creds['sandbox'],
                $data['source_id'],
                $data['device_session_id'],
                $amount,
                'Pedido boutique ' . $order->order_number,
                $order->order_number,
                $customer,
                $request->ip(),
                $request->userAgent()
            );

            if (! $this->openPayService->chargeIsSuccessful($charge)) {
                $st = $charge['status'] ?? 'unknown';

                return ApiResponseHelper::apiError(
                    'El cargo no se completó. Estado: ' . $st,
                    ['openpay_status' => $st],
                    402,
                    'OPENPAY_CHARGE_INCOMPLETE'
                );
            }

            $chargeId = $charge['id'] ?? null;
            $payment->update([
                'status' => 'completado',
                'transaction_reference' => is_string($chargeId) ? $chargeId : null,
                'confirmed_at' => now(),
            ]);
            $order->update(['status' => 'pagado']);

            $order->load(['orderItems', 'payment', 'shipment']);

            return ApiResponseHelper::apiSuccess(200, 'Pago con OpenPay confirmado', [
                'order' => $order,
                'openpay_charge_id' => $chargeId,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('OpenPay confirm charge', [
                'message' => $e->getMessage(),
                'exception' => $e::class,
            ]);

            return ApiResponseHelper::apiError(
                'Error al confirmar el pago OpenPay',
                $e->getMessage(),
                500,
                'OPENPAY_CHARGE_ERROR',
                ['openpay_error' => $e->getMessage()]
            );
        }
    }
}
