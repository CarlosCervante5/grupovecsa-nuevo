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
use App\Services\Boutique\StripeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BoutiqueCheckoutController extends Controller
{
    protected BoutiqueInventoryService $inventoryService;
    protected EnviacomService $enviacomService;
    protected StripeService $stripeService;

    public function __construct(
        BoutiqueInventoryService $inventoryService,
        EnviacomService $enviacomService,
        StripeService $stripeService
    ) {
        $this->inventoryService = $inventoryService;
        $this->enviacomService = $enviacomService;
        $this->stripeService = $stripeService;
    }

    public function shippingQuote(ShippingQuoteRequest $request)
    {
        try {
            $data = $request->validated();

            $destination = [
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

            $quotes = $this->enviacomService->getShippingQuotes($destination, $packages);

            return ApiResponseHelper::apiSuccess(200, 'Cotizaciones de envío obtenidas', ['shipping_options' => $quotes]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener cotizaciones de envío', $e->getMessage(), 500, 'SHIPPING_QUOTE_ERROR');
        }
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
                return ApiResponseHelper::apiError('Stock insuficiente para algunos productos', json_encode($insufficientStock), 400, 'INSUFFICIENT_STOCK');
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
                return ApiResponseHelper::apiError('Stock insuficiente para algunos productos', json_encode($insufficientStock), 400, 'INSUFFICIENT_STOCK');
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
}
