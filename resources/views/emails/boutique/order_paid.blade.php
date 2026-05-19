<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Pago confirmado</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.5; max-width: 600px; margin: 0 auto; padding: 24px;">
    <h1 style="color: #1c69d4; font-size: 22px;">Pago confirmado</h1>
    <p>Hola{{ $order->shipping_name ? ', ' . e($order->shipping_name) : '' }},</p>
    <p>Confirmamos el pago de tu pedido <strong>{{ $order->order_number }}</strong>. Estamos preparando tu envío.</p>

    <p><strong>Total pagado:</strong> ${{ number_format((float) $order->total, 2) }} MXN</p>

    @if ($order->shipment?->tracking_number)
        <p><strong>Guía de envío:</strong> {{ $order->shipment->tracking_number }}</p>
    @endif

    <p style="font-size: 13px; color: #64748b; margin-top: 24px;">Grupo VECSA Boutique</p>
</body>
</html>
