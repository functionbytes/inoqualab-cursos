<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color: #1b2a3a; font-size: 12px; margin: 0; }
        .wrap { padding: 28px 34px; }
        .head { width: 100%; border-bottom: 2px solid #0d1b2a; padding-bottom: 14px; margin-bottom: 22px; }
        .head td { vertical-align: top; }
        .brand { font-size: 20px; font-weight: bold; color: #0d1b2a; }
        .doc { text-align: right; }
        .doc .t { font-size: 16px; font-weight: bold; color: #006fa3; }
        .doc .ref { color: #6a7888; font-size: 11px; }
        .meta { width: 100%; margin-bottom: 20px; }
        .meta td { vertical-align: top; padding-right: 16px; width: 50%; }
        .meta .lbl { color: #8d9db5; font-size: 10px; text-transform: uppercase; letter-spacing: .04em; }
        .meta .val { font-size: 12px; color: #1b2a3a; margin-bottom: 6px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        table.items th { background: #0d1b2a; color: #fff; text-align: left; padding: 8px 10px; font-size: 11px; }
        table.items td { padding: 8px 10px; border-bottom: 1px solid #e7ecf1; }
        table.items td.r, table.items th.r { text-align: right; }
        .totals { width: 45%; margin-left: 55%; }
        .totals td { padding: 5px 10px; }
        .totals td.r { text-align: right; }
        .totals .grand td { border-top: 2px solid #0d1b2a; font-size: 15px; font-weight: bold; color: #006fa3; padding-top: 8px; }
        .badge { display: inline-block; background: #e4f2fb; color: #006fa3; padding: 3px 10px; border-radius: 999px; font-size: 10px; font-weight: bold; }
        .foot { margin-top: 30px; color: #8d9db5; font-size: 10.5px; text-align: center; border-top: 1px solid #e7ecf1; padding-top: 14px; }
    </style>
</head>
<body>
<div class="wrap">

    <table class="head">
        <tr>
            <td><span class="brand">{{ $brand }}</span></td>
            <td class="doc">
                <div class="t">Recibo de compra</div>
                <div class="ref">#{{ $order->reference ?? $order->slack }}</div>
                <div class="ref">{{ \Carbon\Carbon::parse($order->payment_at ?? $order->created_at)->format('Y-m-d H:i') }}</div>
            </td>
        </tr>
    </table>

    <table class="meta">
        <tr>
            <td>
                <div class="lbl">Cliente</div>
                <div class="val">{{ trim(($user->firstname ?? '').' '.($user->lastname ?? '')) ?: 'Cliente' }}</div>
                @if($user?->identification)<div class="val">{{ $user->identification_type }} {{ $user->identification }}</div>@endif
                <div class="val">{{ $user->email ?? '' }}</div>
            </td>
            <td>
                <div class="lbl">Estado</div>
                <div class="val"><span class="badge">{{ optional($order->condition)->title ?? 'Pagada' }}</span></div>
                <div class="lbl" style="margin-top:8px;">Método de pago</div>
                <div class="val">{{ optional($order->method)->title ?? '—' }}</div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Producto</th>
                <th class="r">Cant.</th>
                <th class="r">Precio</th>
                <th class="r">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $it)
                @php
                    $qty = (int) ($it->quantity ?? 1);
                    $line = (float) ($it->amount ?? 0);
                    $unit = $qty > 0 ? $line / $qty : $line;
                @endphp
                <tr>
                    <td>{{ optional($it->itemable)->title ?? 'Producto' }}</td>
                    <td class="r">{{ $qty }}</td>
                    <td class="r">$ {{ number_format($unit, 0, ',', '.') }}</td>
                    <td class="r">$ {{ number_format($line, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        @if($order->total_discount_amount > 0)
            <tr><td>Subtotal</td><td class="r">$ {{ number_format($order->total_before_discount, 0, ',', '.') }} COP</td></tr>
            <tr><td>Descuento{{ optional($order->coupon)->code ? ' ('.$order->coupon->code.')' : '' }}</td><td class="r">– $ {{ number_format($order->total_discount_amount, 0, ',', '.') }} COP</td></tr>
        @endif
        <tr class="grand"><td>Total</td><td class="r">$ {{ number_format($order->total_order_amount, 0, ',', '.') }} COP</td></tr>
    </table>

    <div class="foot">
        Este documento es un comprobante de compra generado por {{ $brand }}.
    </div>

</div>
</body>
</html>
