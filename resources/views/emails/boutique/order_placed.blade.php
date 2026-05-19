<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Pedido registrado</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.5; max-width: 600px; margin: 0 auto; padding: 24px;">
    <h1 style="color: #1c69d4; font-size: 22px;">Gracias por tu pedido</h1>
    <p>Hola{{ $order->shipping_name ? ', ' . e($order->shipping_name) : '' }},</p>
    <p>Recibimos tu pedido <strong>{{ $order->order_number }}</strong>.</p>

    <table cellpadding="8" cellspacing="0" border="0" width="100%" style="border-collapse: collapse; margin: 16px 0;">
        <thead>
            <tr style="background: #f1f5f9;">
                <th align="left">Producto</th>
                <th align="right">Cant.</th>
                <th align="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td>{{ $item->product_name }}</td>
                    <td align="right">{{ $item->quantity }}</td>
                    <td align="right">${{ number_format((float) $item->subtotal, 2) }} MXN</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p><strong>Subtotal:</strong> ${{ number_format((float) $order->subtotal, 2) }} MXN<br>
    <strong>Envío:</strong> ${{ number_format((float) $order->shipping_cost, 2) }} MXN<br>
    <strong>Total:</strong> ${{ number_format((float) $order->total, 2) }} MXN</p>

    @if ($showTransferBank)
        <div style="background: #eff6ff; border: 1px solid #93c5fd; border-radius: 8px; padding: 16px; margin: 20px 0;">
            <h2 style="font-size: 16px; margin: 0 0 8px;">Datos para transferencia bancaria</h2>
            @if ($transferBank['configured'] ?? false)
                <p style="margin: 4px 0;"><strong>Banco:</strong> {{ $transferBank['bank_name'] }}</p>
                <p style="margin: 4px 0;"><strong>Titular:</strong> {{ $transferBank['account_holder'] }}</p>
                <p style="margin: 4px 0;"><strong>CLABE:</strong> {{ $transferBank['clabe'] }}</p>
                @if (!empty($transferBank['account_number']))
                    <p style="margin: 4px 0;"><strong>Cuenta:</strong> {{ $transferBank['account_number'] }}</p>
                @endif
                <p style="margin: 8px 0 0;"><strong>Referencia:</strong> {{ $order->order_number }}</p>
                @if (!empty($transferBank['instructions']))
                    <p style="margin-top: 12px; font-size: 14px;">{{ $transferBank['instructions'] }}</p>
                @endif
            @else
                <p>La tienda te contactará con los datos bancarios. Usa como referencia: <strong>{{ $order->order_number }}</strong>.</p>
            @endif
            <p style="font-size: 13px; color: #64748b; margin-top: 12px;">Tienes 72 horas para realizar el pago; de lo contrario el pedido puede cancelarse.</p>
        </div>
    @elseif ($paymentMethod === 'sucursal')
        <p>Pago en sucursal: presenta tu número de pedido <strong>{{ $order->order_number }}</strong> en cualquiera de nuestras sucursales (reserva 72 h).</p>
    @elseif ($paymentMethod === 'openpay')
        <p>Si el pago con tarjeta quedó pendiente, completa el cobro desde el enlace que viste en el checkout o contacta a la tienda.</p>
    @endif

    <p style="font-size: 13px; color: #64748b; margin-top: 24px;">Grupo VECSA Boutique</p>
</body>
</html>
