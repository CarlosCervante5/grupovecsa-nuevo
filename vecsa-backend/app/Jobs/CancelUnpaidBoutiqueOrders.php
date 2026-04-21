<?php

namespace App\Jobs;

use App\Models\Boutique\BoutiqueOrder;
use App\Services\Boutique\BoutiqueInventoryService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CancelUnpaidBoutiqueOrders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(BoutiqueInventoryService $inventoryService): void
    {
        try {
            $cutoff = Carbon::now()->subHours(72);

            $orders = BoutiqueOrder::where('status', 'pendiente')
                ->whereHas('payment', function ($q) {
                    $q->whereIn('method', ['transferencia', 'sucursal', 'openpay'])
                      ->where('status', 'pendiente');
                })
                ->where('created_at', '<', $cutoff)
                ->with(['orderItems.product', 'payment'])
                ->get();

            foreach ($orders as $order) {
                // Restore inventory
                foreach ($order->orderItems as $orderItem) {
                    if ($orderItem->product) {
                        $inventoryService->restoreStock(
                            $orderItem->product,
                            $orderItem->quantity,
                            'cancelacion',
                            $order->uuid
                        );
                    }
                }

                // Cancel order
                $order->update(['status' => 'cancelado']);

                // Update payment status
                if ($order->payment) {
                    $order->payment->update(['status' => 'fallido']);
                }

                Log::info('Pedido cancelado automáticamente por falta de pago', [
                    'order_uuid' => $order->uuid,
                    'order_number' => $order->order_number,
                ]);
            }

            Log::info('CancelUnpaidBoutiqueOrders: ' . $orders->count() . ' pedidos cancelados');
        } catch (\Exception $e) {
            Log::error('Error en CancelUnpaidBoutiqueOrders: ' . $e->getMessage());
        }
    }
}
