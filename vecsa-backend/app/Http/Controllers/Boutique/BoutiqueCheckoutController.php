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
use App\Services\Boutique\BoutiqueCheckoutLineService;
use App\Services\Boutique\BoutiqueInventoryService;
use App\Services\Boutique\BoutiqueOpenPayCheckoutService;
use App\Services\Boutique\BoutiqueOrderMailService;
use App\Services\Boutique\EnviacomService;
use App\Services\Boutique\OpenPayChargeException;
use App\Services\Boutique\OpenPayService;
use App\Services\Boutique\StripeService;
use App\Support\BoutiqueCheckoutPaymentMethods;
use App\Support\BoutiqueTransferBankDetails;
use Exception;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class BoutiqueCheckoutController extends Controller
{
    protected BoutiqueInventoryService $inventoryService;
    protected EnviacomService $enviacomService;
    protected StripeService $stripeService;

    protected OpenPayService $openPayService;

    protected BoutiqueCheckoutLineService $checkoutLineService;

    protected BoutiqueOrderMailService $orderMailService;

    protected BoutiqueOpenPayCheckoutService $openPayCheckoutService;

    public function __construct(
        BoutiqueInventoryService $inventoryService,
        EnviacomService $enviacomService,
        StripeService $stripeService,
        OpenPayService $openPayService,
        BoutiqueCheckoutLineService $checkoutLineService,
        BoutiqueOrderMailService $orderMailService,
        BoutiqueOpenPayCheckoutService $openPayCheckoutService,
    ) {
        $this->inventoryService = $inventoryService;
        $this->enviacomService = $enviacomService;
        $this->stripeService = $stripeService;
        $this->openPayService = $openPayService;
        $this->checkoutLineService = $checkoutLineService;
        $this->orderMailService = $orderMailService;
        $this->openPayCheckoutService = $openPayCheckoutService;
    }

    /**
     * Cobra con OpenPay y crea el pedido solo si el cargo se confirma (sin pedido previo pendiente).
     * Invitados: body como create_guest_order + source_id + device_session_id.
     * Sesión: mismo body que create_order + tokens OpenPay (Bearer opcional en ruta pública).
     */
    public function placeOpenPayOrder(Request $request)
    {
        try {
            $result = $this->openPayCheckoutService->placeOrder($request);

            if (! empty($result['requires_3ds'])) {
                return ApiResponseHelper::apiSuccess(200, 'Se requiere autenticación 3D Secure', [
                    'requires_3ds' => true,
                    'redirect_url' => $result['redirect_url'],
                    'order_number' => $result['order_number'],
                ]);
            }

            $order = $result['order'];
            $order->load(['orderItems', 'payment', 'shipment']);

            return ApiResponseHelper::apiSuccess(201, 'Pedido creado y pago confirmado', ['order' => $order]);
        } catch (OpenPayChargeException $e) {
            Log::warning('OpenPay place order', [
                'message' => $e->getMessage(),
                'http_status' => $e->httpStatus,
                'openpay_code' => $e->openpayErrorCode,
            ]);

            return ApiResponseHelper::apiError(
                $e->getMessage(),
                [
                    'openpay_error' => $e->getMessage(),
                    'openpay_code' => $e->openpayErrorCode,
                    'openpay_body' => $e->openpayBody,
                ],
                $e->httpStatus >= 400 && $e->httpStatus < 500 ? $e->httpStatus : 422,
                'OPENPAY_CHARGE_ERROR'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('OpenPay place order', ['message' => $e->getMessage()]);

            return ApiResponseHelper::apiError('Error al procesar el pago', $e->getMessage(), 500, 'OPENPAY_PLACE_ORDER_ERROR');
        }
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

            try {
                $resolved = $this->checkoutLineService->resolveLines($data['items']);
            } catch (Exception $e) {
                $code = $e->getMessage();
                if (str_starts_with($code, 'PRODUCT_NOT_FOUND:')) {
                    return ApiResponseHelper::apiError('Producto no encontrado', null, 404, 'PRODUCT_NOT_FOUND');
                }
                if ($code === 'PRODUCT_VARIANT_NOT_FOUND') {
                    return ApiResponseHelper::apiError('La variante del producto no es válida', null, 422, 'PRODUCT_VARIANT_NOT_FOUND');
                }
                throw $e;
            }

            if (! empty($resolved['insufficient'])) {
                return ApiResponseHelper::apiError(
                    'Stock insuficiente para algunos productos',
                    ['items' => $resolved['insufficient']],
                    400,
                    'INSUFFICIENT_STOCK'
                );
            }

            if (! BoutiqueCheckoutPaymentMethods::isMethodEnabled($data['payment_method'])) {
                return ApiResponseHelper::apiError('El método de pago seleccionado no está habilitado', null, 422, 'PAYMENT_METHOD_DISABLED');
            }

            $lines = $resolved['lines'];
            $subtotal = round(array_sum(array_column($lines, 'subtotal')), 2);

            $shippingCost = 0;
            if ($data['delivery_method'] === 'envio_domicilio' && !empty($data['shipping_option'])) {
                $shippingCost = $data['shipping_option']['price'] ?? 0;
            }

            $total = $subtotal + $shippingCost;

            $today = Carbon::now()->format('Ymd');
            $dailyCount = BoutiqueOrder::whereDate('created_at', Carbon::today())->count() + 1;
            $orderNumber = 'BOUT-' . $today . '-' . str_pad($dailyCount, 4, '0', STR_PAD_LEFT);

            $order = DB::transaction(function () use ($data, $lines, $subtotal, $shippingCost, $total, $orderNumber) {
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

                foreach ($lines as $line) {
                    $this->createOrderItemFromLine($order, $line);
                    $this->checkoutLineService->reduceLineStock($line, $order->uuid);
                }

                $this->createPaymentAndShipment($order, $data, $total);

                return $order;
            });

            return $this->orderCreatedResponse($order, $data['payment_method']);
        } catch (\Exception $e) {
            Log::error('Boutique createGuestOrder', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

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

            $cartItemsPayload = $cart->items->map(function ($item) {
                $row = [
                    'product_uuid' => $item->product->uuid,
                    'quantity' => $item->quantity,
                ];
                if (! empty($item->variant_uuid)) {
                    $row['variant_uuid'] = $item->variant_uuid;
                }

                return $row;
            })->all();

            try {
                $resolved = $this->checkoutLineService->resolveLines($cartItemsPayload);
            } catch (Exception $e) {
                $code = $e->getMessage();
                if (str_starts_with($code, 'PRODUCT_NOT_FOUND:')) {
                    return ApiResponseHelper::apiError('Producto no encontrado en el carrito', null, 404, 'PRODUCT_NOT_FOUND');
                }
                if ($code === 'PRODUCT_VARIANT_NOT_FOUND') {
                    return ApiResponseHelper::apiError('La variante del producto no es válida', null, 422, 'PRODUCT_VARIANT_NOT_FOUND');
                }
                throw $e;
            }

            if (! empty($resolved['insufficient'])) {
                return ApiResponseHelper::apiError(
                    'Stock insuficiente para algunos productos',
                    ['items' => $resolved['insufficient']],
                    400,
                    'INSUFFICIENT_STOCK'
                );
            }

            if (! BoutiqueCheckoutPaymentMethods::isMethodEnabled($data['payment_method'])) {
                return ApiResponseHelper::apiError('El método de pago seleccionado no está habilitado', null, 422, 'PAYMENT_METHOD_DISABLED');
            }

            $lines = $resolved['lines'];
            $subtotal = round(array_sum(array_column($lines, 'subtotal')), 2);

            $shippingCost = 0;
            if ($data['delivery_method'] === 'envio_domicilio' && !empty($data['shipping_option'])) {
                $shippingCost = $data['shipping_option']['price'] ?? 0;
            }

            $total = $subtotal + $shippingCost;

            // Generate order number: BOUT-YYYYMMDD-XXXX
            $today = Carbon::now()->format('Ymd');
            $dailyCount = BoutiqueOrder::whereDate('created_at', Carbon::today())->count() + 1;
            $orderNumber = 'BOUT-' . $today . '-' . str_pad($dailyCount, 4, '0', STR_PAD_LEFT);

            $order = DB::transaction(function () use ($data, $user, $cart, $lines, $subtotal, $shippingCost, $total, $orderNumber) {
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

                foreach ($lines as $line) {
                    $this->createOrderItemFromLine($order, $line);
                    $this->checkoutLineService->reduceLineStock($line, $order->uuid);
                }

                $this->createPaymentAndShipment($order, $data, $total);

                foreach ($cart->items as $item) {
                    $item->delete();
                }

                return $order;
            });

            return $this->orderCreatedResponse($order, $data['payment_method']);
        } catch (\Exception $e) {
            Log::error('Boutique createOrder', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

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
     * Confirmación síncrona; reconciliación asíncrona vía POST /api/boutique/webhook/openpay.
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

            try {
                $customer = $this->openPayService->buildCustomerFromOrder($order);
            } catch (OpenPayChargeException $e) {
                return ApiResponseHelper::apiError(
                    $e->getMessage(),
                    ['openpay_error' => $e->getMessage()],
                    422,
                    'OPENPAY_CUSTOMER_INVALID'
                );
            }

            if (! str_starts_with($creds['private_key'], 'sk_')) {
                Log::warning('OpenPay: llave privada ausente o no descifrable', [
                    'merchant_id' => $creds['merchant_id'],
                    'sandbox' => $creds['sandbox'],
                ]);

                return ApiResponseHelper::apiError(
                    'La llave privada de OpenPay no está disponible en el servidor. Guarde de nuevo las credenciales en administración (mismo comercio y modo sandbox).',
                    ['openpay_error' => 'OPENPAY_PRIVATE_KEY_INVALID'],
                    503,
                    'OPENPAY_NOT_CONFIGURED'
                );
            }

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

            if ($this->openPayService->chargeRequires3ds($charge)) {
                return ApiResponseHelper::apiSuccess(200, 'Se requiere autenticación 3D Secure', [
                    'requires_3ds' => true,
                    'redirect_url' => $this->openPayService->charge3dsRedirectUrl($charge),
                    'order_uuid' => $order->uuid,
                ]);
            }

            if (! $this->openPayService->chargeIsSuccessful($charge)) {
                $st = $charge['status'] ?? 'unknown';

                return ApiResponseHelper::apiError(
                    'El cargo no se completó. Estado: ' . $st,
                    ['openpay_status' => $st, 'openpay_error' => $charge['description'] ?? null],
                    422,
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

            try {
                $this->orderMailService->sendOrderPaid($order);
            } catch (\Throwable $mailEx) {
                Log::warning('OpenPay: pago OK pero falló correo', [
                    'order_uuid' => $order->uuid,
                    'message' => $mailEx->getMessage(),
                ]);
            }

            return ApiResponseHelper::apiSuccess(200, 'Pago con OpenPay confirmado', [
                'order' => $order,
                'openpay_charge_id' => $chargeId,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (OpenPayChargeException $e) {
            Log::warning('OpenPay confirm charge API', [
                'message' => $e->getMessage(),
                'http_status' => $e->httpStatus,
                'openpay_code' => $e->openpayErrorCode,
                'body' => $e->openpayBody,
            ]);

            return ApiResponseHelper::apiError(
                $e->getMessage(),
                [
                    'openpay_error' => $e->getMessage(),
                    'openpay_code' => $e->openpayErrorCode,
                    'openpay_body' => $e->openpayBody,
                    'openpay_http_status' => $e->httpStatus,
                ],
                422,
                'OPENPAY_CHARGE_ERROR'
            );
        } catch (\Exception $e) {
            Log::error('OpenPay confirm charge', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ApiResponseHelper::apiError(
                'Error al confirmar el pago OpenPay',
                ['openpay_error' => $e->getMessage()],
                500,
                'OPENPAY_CHARGE_ERROR'
            );
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createPaymentAndShipment(BoutiqueOrder $order, array $data, float $total): void
    {
        BoutiquePayment::create([
            'order_id' => $order->id,
            'method' => $data['payment_method'],
            'amount' => $total,
            'status' => 'pendiente',
        ]);

        $shipmentData = [
            'order_id' => $order->id,
            'delivery_method' => $data['delivery_method'],
            'status' => 'pendiente',
        ];

        if ($data['delivery_method'] === 'recoleccion_sucursal' && ! empty($data['dealership_uuid'])) {
            $dealership = \App\Support\DealershipLookup::findByUuidOrId($data['dealership_uuid']);
            if ($dealership) {
                $shipmentData['dealership_id'] = $dealership->id;
            }
        }

        if ($data['delivery_method'] === 'envio_domicilio' && ! empty($data['shipping_option'])) {
            $shipmentData['carrier_name'] = $data['shipping_option']['carrier'] ?? null;
            $shipmentData['estimated_delivery'] = isset($data['shipping_option']['estimated_days'])
                ? Carbon::now()->addDays($data['shipping_option']['estimated_days'])->toDateString()
                : null;
        }

        BoutiqueShipment::create($shipmentData);
    }

    private function orderCreatedResponse(BoutiqueOrder $order, string $paymentMethod)
    {
        $order->load(['orderItems', 'payment', 'shipment']);

        try {
            $this->orderMailService->sendOrderPlaced($order);
        } catch (\Throwable $e) {
            Log::warning('Boutique: pedido creado pero falló encolar correo', [
                'order_uuid' => $order->uuid,
                'message' => $e->getMessage(),
            ]);
        }

        $payload = ['order' => $order];
        if ($paymentMethod === 'transferencia') {
            $payload['transfer_bank'] = BoutiqueTransferBankDetails::publicPayload();
        }

        return ApiResponseHelper::apiSuccess(201, 'Pedido creado exitosamente', $payload);
    }

    /**
     * @param  array{product: \App\Models\Boutique\BoutiqueProduct, variant: ?\App\Models\Boutique\BoutiqueProductVariant, quantity: int, unit_price: float, subtotal: float, product_name: string, product_sku: string}  $line
     */
    private function createOrderItemFromLine(BoutiqueOrder $order, array $line): void
    {
        $product = $line['product'];
        $variant = $line['variant'];

        $attrs = [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $line['product_name'],
            'product_sku' => $line['product_sku'],
            'quantity' => $line['quantity'],
            'unit_price' => $line['unit_price'],
            'subtotal' => $line['subtotal'],
        ];

        if ($variant !== null && $this->orderItemsTableHasVariantId()) {
            $attrs['product_variant_id'] = $variant->id;
        }

        BoutiqueOrderItem::create($attrs);
    }

    private function orderItemsTableHasVariantId(): bool
    {
        static $has = null;
        if ($has === null) {
            $has = Schema::hasColumn((new BoutiqueOrderItem)->getTable(), 'product_variant_id');
        }

        return $has;
    }
}
