<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden {{ $order->slack }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; color: #333; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        h2 { color: #008bce; border-bottom: 2px solid #008bce; padding-bottom: 8px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 20px 0; }
        .info-item { padding: 8px; background: #f8f9fa; border-radius: 4px; }
        .info-item label { font-weight: bold; display: block; color: #666; font-size: 11px; }
        .info-item span { font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #008bce; color: #fff; padding: 8px; text-align: left; }
        td { padding: 8px; border-bottom: 1px solid #ddd; }
        .text-end { text-align: right; }
        .total-row td { font-weight: bold; background: #f0f0f0; }
        @media print {
            .no-print { display: none; }
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="no-print" style="margin-bottom:15px;">
            <button onclick="window.print()" style="background:#008bce;color:#fff;border:none;padding:8px 20px;border-radius:4px;cursor:pointer;">
                Imprimir
            </button>
            <button onclick="window.history.back()" style="background:#6c757d;color:#fff;border:none;padding:8px 20px;border-radius:4px;cursor:pointer;margin-left:8px;">
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
                    <td colspan="3" style="text-align:center;color:#999;">Sin ítems registrados.</td>
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
</body>
</html>
