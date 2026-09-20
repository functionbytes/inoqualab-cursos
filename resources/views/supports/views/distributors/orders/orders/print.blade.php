<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden {{ $order->slack }}</title>
    <link rel="stylesheet" href="{{ asset('supports/css/views/distributors/orders/orders/print.css') }}">
</head>
<body>
    <div class="container">
        <div class="no-print print-actions">
            <button class="btn-print">
                Imprimir
            </button>
            <button class="btn-back">
                Volver
            </button>
        </div>

        <h2>Orden de pedido</h2>
        <p><strong>Código:</strong> {{ $order->slack }}</p>

        <div class="info-grid">
            <div class="info-item">
                <label>Fecha</label>
                <span>{{ $order->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="info-item">
                <label>Estado</label>
                <span>{{ optional($order->condition)->title ?? '-' }}</span>
            </div>
            <div class="info-item">
                <label>Método de pago</label>
                <span>{{ optional($order->method)->title ?? '-' }}</span>
            </div>
            <div class="info-item">
                <label>Subtotal</label>
                <span>{{ formatPrice($order->subtotal) }}</span>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Descripción</th>
                    <th class="text-end">Valor</th>
                </tr>
            </thead>
            <tbody>
                @if($order->inscriptions && $order->inscriptions->count())
                @foreach($order->inscriptions as $i => $inscription)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ optional($inscription->course)->title ?? 'Curso' }}</td>
                    <td class="text-end">{{ formatPrice($inscription->price ?? 0) }}</td>
                </tr>
                @endforeach
                @else
                <tr>
                    <td colspan="3" class="empty-items">Sin ítems registrados.</td>
                </tr>
                @endif
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="2">Total</td>
                    <td class="text-end">{{ formatPrice($order->total) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    <script src="{{ asset('supports/js/views/distributors/orders/orders/print.js') }}"></script>
</body>
</html>
