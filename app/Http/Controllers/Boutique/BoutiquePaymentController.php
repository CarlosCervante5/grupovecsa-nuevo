<?php

namespace App\Http\Controllers\Boutique;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Boutique\ConfirmManualPaymentRequest;
use App\Models\Boutique\BoutiqueOrder;
use App\Services\Boutique\BoutiqueOrderMailService;
use App\Services\Boutique\OpenPayService;
use App\Services\Boutique\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BoutiquePaymentController extends Controller
{
    protected StripeService $stripeService;

    protected OpenPayService $openPayService;

    public function __construct(StripeService $stripeService, OpenPayService $openPayService)
    {
        $this->stripeService = $stripeService;
        $this->openPayService = $openPayService;
    }

    public function stripeWebhook(Request $request)
    {
        try {
            $payload = $request->getContent();
            $sigHeader = $request->header('Stripe-Signature');

            $event = $this->stripeService->verifyWebhookSignature($payload, $sigHeader);

            $type = $event['type'] ?? null;

            if ($type === 'payment_intent.succeeded') {
                $this->stripeService->processPaymentSucceeded($event);
            }

            return response()->json(['status' => 200, 'message' => 'Webhook procesado'], 200);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al procesar webhook de Stripe', $e->getMessage(), 400, 'STRIPE_WEBHOOK_ERROR');
        }
    }

    /**
     * Webhook OpenPay (basic auth). Registrar URL en el dashboard OpenPay con los mismos user/password.
     */
    public function openpayWebhook(Request $request)
    {
        if (! $this->openPayService->verifyWebhookAuth($request->getUser(), $request->getPassword())) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $payload = json_decode($request->getContent(), true);
        if (! is_array($payload)) {
            return response()->json(['message' => 'JSON inválido'], 400);
        }

        try {
            $this->openPayService->processWebhook($payload);
        } catch (\Exception $e) {
            Log::error('OpenPay webhook error', ['message' => $e->getMessage()]);
        }

        return response()->json(['status' => 'ok'], 200);
    }

    public function confirmManual(ConfirmManualPaymentRequest $request)
    {
        try {
            $data = $request->validated();

            $order = BoutiqueOrder::findByUuid($data['order_uuid']);
            if (!$order) {
                return ApiResponseHelper::apiError('El pedido no existe', null, 404, 'ORDER_NOT_FOUND');
            }

            $payment = $order->payment;
            if (!$payment) {
                return ApiResponseHelper::apiError('No se encontró el pago asociado', null, 404, 'PAYMENT_NOT_FOUND');
            }

            $wasCompleted = $payment->status === 'completado';

            $payment->update([
                'status' => 'completado',
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'confirmed_at' => now(),
            ]);

            $order->update(['status' => 'pagado']);

            if (! $wasCompleted) {
                app(BoutiqueOrderMailService::class)->sendOrderPaid($order->fresh(['payment']));
            }

            return ApiResponseHelper::apiSuccess(200, 'Pago confirmado exitosamente', [
                'order' => $order->fresh(['payment']),
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al confirmar el pago', $e->getMessage(), 500, 'CONFIRM_PAYMENT_ERROR');
        }
    }
}
