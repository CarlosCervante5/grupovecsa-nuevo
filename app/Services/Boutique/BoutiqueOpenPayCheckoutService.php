<?php

namespace App\Services\Boutique;

use App\Models\Boutique\BoutiqueCart;
use App\Models\Boutique\BoutiqueOrder;
use App\Models\Boutique\BoutiqueOrderItem;
use App\Models\Boutique\BoutiquePayment;
use App\Models\Boutique\BoutiqueShipment;
use App\Models\Dealership;
use App\Models\User;
use App\Support\BoutiqueCheckoutPaymentMethods;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Checkout OpenPay: cobra primero y persiste el pedido solo si el cargo se confirma (o vía webhook 3DS).
 */
class BoutiqueOpenPayCheckoutService
{
    public const PENDING_CACHE_PREFIX = 'boutique_openpay_pending:';

    public function __construct(
        protected BoutiqueCheckoutLineService $checkoutLineService,
        protected OpenPayService $openPayService,
        protected BoutiqueOrderMailService $orderMailService,
    ) {}

    /**
     * @return array{order: BoutiqueOrder}|array{requires_3ds: true, redirect_url: string, order_number: string}
     */
    public function placeOrder(Request $request): array
    {
        $request->validate([
            'source_id' => 'required|string|max:45',
            'device_session_id' => 'required|string|size:32',
        ]);

        if (! BoutiqueCheckoutPaymentMethods::isMethodEnabled('openpay')) {
            throw new OpenPayChargeException('OpenPay no está habilitado.', 422);
        }

        $ctx = $this->resolveCheckoutContext($request);
        $creds = $this->openPayService->getActiveCredentials();
        if ($creds['merchant_id'] === '' || ! str_starts_with($creds['private_key'], 'sk_')) {
            throw new OpenPayChargeException(
                'La llave privada de OpenPay no está disponible en el servidor. Guarde de nuevo las credenciales en administración.',
                503
            );
        }

        $customer = $this->openPayService->buildCustomerFromCheckoutData($ctx['data'], $ctx['user']);
        $orderNumber = $this->generateOrderNumber();

        $charge = $this->openPayService->createMerchantCardCharge(
            $creds['merchant_id'],
            $creds['private_key'],
            $creds['sandbox'],
            (string) $request->input('source_id'),
            (string) $request->input('device_session_id'),
            $ctx['total'],
            'Pedido boutique ' . $orderNumber,
            $orderNumber,
            $customer,
            $request->ip(),
            $request->userAgent()
        );

        if ($this->openPayService->chargeRequires3ds($charge)) {
            $redirectUrl = $this->openPayService->charge3dsRedirectUrl($charge);
            if ($redirectUrl === null) {
                throw new OpenPayChargeException('Se requiere 3D Secure pero OpenPay no devolvió URL.', 422);
            }

            Cache::put(self::PENDING_CACHE_PREFIX . $orderNumber, [
                'data' => $ctx['data'],
                'lines' => $ctx['lines'],
                'user_id' => $ctx['user']?->id,
                'cart_id' => $ctx['cart']?->id,
                'subtotal' => $ctx['subtotal'],
                'shipping_cost' => $ctx['shipping_cost'],
                'total' => $ctx['total'],
                'order_number' => $orderNumber,
            ], now()->addHours(2));

            return [
                'requires_3ds' => true,
                'redirect_url' => $redirectUrl,
                'order_number' => $orderNumber,
            ];
        }

        if (! $this->openPayService->chargeIsSuccessful($charge)) {
            $st = $charge['status'] ?? 'unknown';
            throw new OpenPayChargeException('El cargo no se completó. Estado: ' . $st, 422);
        }

        $chargeId = is_string($charge['id'] ?? null) ? $charge['id'] : null;
        $order = $this->persistPaidOrder($ctx, $orderNumber, $chargeId);

        return ['order' => $order];
    }

    public function finalizePendingFromWebhook(string $orderNumber, ?string $chargeId): ?BoutiqueOrder
    {
        $key = self::PENDING_CACHE_PREFIX . $orderNumber;
        $pending = Cache::get($key);
        if (! is_array($pending)) {
            return null;
        }

        $existing = BoutiqueOrder::where('order_number', $orderNumber)->first();
        if ($existing) {
            Cache::forget($key);
            if ($existing->payment && $existing->payment->status !== 'completado') {
                $existing->payment->update([
                    'status' => 'completado',
                    'transaction_reference' => $chargeId,
                    'confirmed_at' => now(),
                ]);
                $existing->update(['status' => 'pagado']);
            }

            return $existing;
        }

        $ctx = [
            'data' => $pending['data'],
            'lines' => $pending['lines'],
            'user' => ! empty($pending['user_id']) ? User::find($pending['user_id']) : null,
            'cart' => ! empty($pending['cart_id']) ? BoutiqueCart::find($pending['cart_id']) : null,
            'subtotal' => (float) $pending['subtotal'],
            'shipping_cost' => (float) $pending['shipping_cost'],
            'total' => (float) $pending['total'],
        ];

        $order = $this->persistPaidOrder($ctx, $orderNumber, $chargeId);
        Cache::forget($key);

        return $order;
    }

    /**
     * @return array{data: array<string, mixed>, lines: array, user: ?User, cart: ?BoutiqueCart, subtotal: float, shipping_cost: float, total: float}
     */
    protected function resolveCheckoutContext(Request $request): array
    {
        $user = $request->user('sanctum') ?? $this->userFromBearerToken($request);

        if ($user) {
            $data = $request->validate([
                'delivery_method' => 'required|in:envio_domicilio,recoleccion_sucursal',
                'shipping_name' => 'required_if:delivery_method,envio_domicilio|string',
                'shipping_address' => 'required_if:delivery_method,envio_domicilio|string',
                'shipping_city' => 'required_if:delivery_method,envio_domicilio|string',
                'shipping_state' => 'required_if:delivery_method,envio_domicilio|string',
                'shipping_zip' => 'required_if:delivery_method,envio_domicilio|string',
                'shipping_phone' => 'required_if:delivery_method,envio_domicilio|string',
                'dealership_uuid' => 'required_if:delivery_method,recoleccion_sucursal|string',
                'shipping_option' => 'nullable|array',
                'notes' => 'nullable|string',
            ]);
            $data['payment_method'] = 'openpay';

            $cart = BoutiqueCart::where('user_id', $user->id)->with(['items.product'])->first();
            if (! $cart || $cart->items->isEmpty()) {
                throw new OpenPayChargeException('El carrito está vacío.', 400);
            }

            $items = $cart->items->map(fn ($item) => array_filter([
                'product_uuid' => $item->product->uuid,
                'quantity' => $item->quantity,
                'variant_uuid' => $item->variant_uuid ?: null,
            ]))->all();
        } else {
            $data = $request->validate([
                'guest_name' => 'required|string|max:255',
                'guest_email' => 'required|email|max:255',
                'delivery_method' => 'required|in:envio_domicilio,recoleccion_sucursal',
                'shipping_name' => 'nullable|string|max:255',
                'shipping_address' => 'nullable|string|max:500',
                'shipping_city' => 'nullable|string|max:100',
                'shipping_state' => 'nullable|string|max:100',
                'shipping_zip' => 'nullable|string|max:10',
                'shipping_phone' => 'nullable|string|max:20',
                'dealership_uuid' => 'nullable|string',
                'shipping_option' => 'nullable|array',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.product_uuid' => 'required|string',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.variant_uuid' => 'nullable|string',
            ]);
            $data['payment_method'] = 'openpay';
            $items = $data['items'];
            $cart = null;
        }

        try {
            $resolved = $this->checkoutLineService->resolveLines($items);
        } catch (Exception $e) {
            $code = $e->getMessage();
            if (str_starts_with($code, 'PRODUCT_NOT_FOUND:') || $code === 'PRODUCT_VARIANT_NOT_FOUND') {
                throw new OpenPayChargeException(
                    $code === 'PRODUCT_VARIANT_NOT_FOUND'
                        ? 'La variante del producto no es válida.'
                        : 'Producto no encontrado.',
                    422
                );
            }
            throw $e;
        }

        if (! empty($resolved['insufficient'])) {
            throw new OpenPayChargeException('Stock insuficiente para algunos productos.', 400);
        }

        $lines = $resolved['lines'];
        $subtotal = round(array_sum(array_column($lines, 'subtotal')), 2);
        $shippingCost = 0.0;
        if ($data['delivery_method'] === 'envio_domicilio' && ! empty($data['shipping_option'])) {
            $shippingCost = (float) ($data['shipping_option']['price'] ?? 0);
        }
        $total = $subtotal + $shippingCost;

        return [
            'data' => $data,
            'lines' => $lines,
            'user' => $user,
            'cart' => $cart ?? null,
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'total' => $total,
        ];
    }

    /**
     * @param  array{data: array<string, mixed>, lines: array, user: ?User, cart: ?BoutiqueCart, subtotal: float, shipping_cost: float, total: float}  $ctx
     */
    protected function persistPaidOrder(array $ctx, string $orderNumber, ?string $chargeId): BoutiqueOrder
    {
        $data = $ctx['data'];
        $user = $ctx['user'];
        $cart = $ctx['cart'];

        return DB::transaction(function () use ($ctx, $data, $user, $cart, $orderNumber, $chargeId) {
            $orderAttrs = [
                'order_number' => $orderNumber,
                'status' => 'pagado',
                'subtotal' => $ctx['subtotal'],
                'shipping_cost' => $ctx['shipping_cost'],
                'total' => $ctx['total'],
                'delivery_method' => $data['delivery_method'],
                'shipping_name' => $data['shipping_name'] ?? null,
                'shipping_address' => $data['shipping_address'] ?? null,
                'shipping_city' => $data['shipping_city'] ?? null,
                'shipping_state' => $data['shipping_state'] ?? null,
                'shipping_zip' => $data['shipping_zip'] ?? null,
                'shipping_phone' => $data['shipping_phone'] ?? null,
                'notes' => $data['notes'] ?? null,
            ];

            if ($user) {
                $orderAttrs['user_id'] = $user->id;
            } else {
                $orderAttrs['guest_name'] = $data['guest_name'];
                $orderAttrs['guest_email'] = $data['guest_email'];
            }

            $order = BoutiqueOrder::create($orderAttrs);

            foreach ($ctx['lines'] as $line) {
                $this->createOrderItemFromLine($order, $line);
                $this->checkoutLineService->reduceLineStock($line, $order->uuid);
            }

            BoutiquePayment::create([
                'order_id' => $order->id,
                'method' => 'openpay',
                'amount' => $ctx['total'],
                'status' => 'completado',
                'transaction_reference' => $chargeId,
                'confirmed_at' => now(),
            ]);

            $this->createShipment($order, $data);

            if ($cart) {
                foreach ($cart->items as $item) {
                    $item->delete();
                }
            }

            $order->load(['orderItems', 'payment', 'shipment']);

            try {
                $this->orderMailService->sendOrderPlaced($order);
                $this->orderMailService->sendOrderPaid($order);
            } catch (\Throwable $e) {
                Log::warning('OpenPay place order: correo falló', [
                    'order_uuid' => $order->uuid,
                    'message' => $e->getMessage(),
                ]);
            }

            return $order;
        });
    }

    /**
     * @param  array{product: \App\Models\Boutique\BoutiqueProduct, variant: ?\App\Models\Boutique\BoutiqueProductVariant, quantity: int, unit_price: float, subtotal: float, product_name: string, product_sku: string}  $line
     */
    protected function createOrderItemFromLine(BoutiqueOrder $order, array $line): void
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

    /**
     * @param  array<string, mixed>  $data
     */
    protected function createShipment(BoutiqueOrder $order, array $data): void
    {
        $shipmentData = [
            'order_id' => $order->id,
            'delivery_method' => $data['delivery_method'],
            'status' => 'pendiente',
        ];

        if ($data['delivery_method'] === 'recoleccion_sucursal' && ! empty($data['dealership_uuid'])) {
            $dealership = Dealership::where('uuid', $data['dealership_uuid'])->first();
            if ($dealership) {
                $shipmentData['dealership_id'] = $dealership->id;
            }
        }

        if ($data['delivery_method'] === 'envio_domicilio' && ! empty($data['shipping_option'])) {
            $shipmentData['carrier_name'] = $data['shipping_option']['carrier'] ?? null;
            $shipmentData['estimated_delivery'] = isset($data['shipping_option']['estimated_days'])
                ? Carbon::now()->addDays((int) $data['shipping_option']['estimated_days'])->toDateString()
                : null;
        }

        BoutiqueShipment::create($shipmentData);
    }

    protected function userFromBearerToken(Request $request): ?User
    {
        $token = $request->bearerToken();
        if (! $token) {
            return null;
        }

        $access = PersonalAccessToken::findToken($token);

        return $access?->tokenable instanceof User ? $access->tokenable : null;
    }

    protected function generateOrderNumber(): string
    {
        $today = Carbon::now()->format('Ymd');
        $dailyCount = BoutiqueOrder::whereDate('created_at', Carbon::today())->count() + 1;

        return 'BOUT-' . $today . '-' . str_pad((string) $dailyCount, 4, '0', STR_PAD_LEFT);
    }

    protected function orderItemsTableHasVariantId(): bool
    {
        static $has = null;
        if ($has === null) {
            $has = Schema::hasColumn((new BoutiqueOrderItem)->getTable(), 'product_variant_id');
        }

        return $has;
    }
}
