<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @font-face {
            font-family: 'Plus Jakarta Sans';
            font-weight: 400;
            src: url({{ storage_path('fonts/PlusJakartaSans-Regular.ttf') }}) format("truetype");
        }
        @font-face {
            font-family: 'Plus Jakarta Sans';
            font-weight: 700;
            src: url({{ storage_path('fonts/PlusJakartaSans-Bold.ttf') }}) format("truetype");
        }
        * { box-sizing: border-box; }
        /* Mismo lenguaje que el diseño "Comprobante" del panel: líneas finas,
           sin cabeceras oscuras, sello de estado. CSS inline porque DomPDF no
           carga hojas externas (excepción documentada en blade-views.md). */
        body { font-family: 'Plus Jakarta Sans', sans-serif; color: #0d1b2a; font-size: 12px; margin: 0; }
        .wrap { padding: 40px 44px; }
        .head { width: 100%; border-bottom: 1px solid #e3e7ea; padding-bottom: 18px; margin-bottom: 22px; }
        .head td { vertical-align: bottom; }
        .brand-logo { height: 38px; width: auto; }
        .brand { font-size: 20px; font-weight: bold; color: #0d1b2a; }
        .kind { color: #5f7182; font-size: 11px; margin-top: 6px; }
        .doc { text-align: right; }
        .doc .lbl { color: #5f7182; font-size: 11px; }
        .doc .num { font-size: 26px; font-weight: bold; color: #0d1b2a; }
        .stamp-cell { text-align: center; }
        .stamp { display: inline-block; border: 1.5px solid #008bce; color: #008bce; border-radius: 5px; padding: 5px 14px 4px; transform: rotate(-7deg); }
        .stamp .s { font-size: 14px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; }
        .stamp .d { font-size: 9px; font-weight: bold; }
        .meta { width: 100%; margin-bottom: 24px; border-bottom: 1px solid #eef0f2; padding-bottom: 16px; }
        .meta td { vertical-align: top; padding-right: 16px; width: 50%; }
        .meta .lbl { color: #5f7182; font-size: 10.5px; margin-bottom: 2px; }
        .meta .val { font-size: 12px; color: #0d1b2a; margin-bottom: 8px; }
        .meta .name { font-size: 14px; font-weight: bold; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 18px; table-layout: fixed; }
        table.items th { color: #5f7182; font-weight: normal; text-align: left; padding: 0 0 8px; font-size: 10.5px; border-bottom: 1px solid #e3e7ea; }
        table.items td { padding: 10px 0; border-bottom: 1px solid #eef0f2; }
        table.items td.r, table.items th.r { text-align: right; white-space: nowrap; }
        table.items th.c-product { width: 58%; }
        table.items th.c-qty { width: 10%; }
        table.items th.c-price, table.items th.c-subtotal { width: 16%; }
        .totals { width: 45%; margin-left: 55%; }
        .totals td { padding: 5px 0; white-space: nowrap; color: #5f7182; }
        .totals td.r { text-align: right; }
        .totals .grand td { border-top: 1px solid #e3e7ea; font-size: 16px; font-weight: bold; color: #0d1b2a; padding-top: 10px; }
        .foot { margin-top: 44px; color: #5f7182; font-size: 10px; text-align: center; }
    </style>
</head>
<body>
<div class="wrap">

    <table class="head">
        <tr>
            <td>
                @if($logo)
                    <img class="brand-logo" src="{{ $logo }}" alt="{{ $brand }}">
                @else
                    <span class="brand">{{ $brand }}</span>
                @endif
                <div class="kind">Recibo de compra</div>
            </td>
            <td class="stamp-cell">
                <div class="stamp">
                    <div class="s">{{ optional($order->condition)->title ?? 'Pagada' }}</div>
                    @if($order->payment_at)
                        <div class="d">{{ \Carbon\Carbon::parse($order->payment_at)->locale('es')->translatedFormat('j M Y') }}</div>
                    @endif
                </div>
            </td>
            <td class="doc">
                <div class="lbl">Referencia</div>
                <div class="num">{{ $order->reference ?? $order->slack }}</div>
            </td>
        </tr>
    </table>

    <table class="meta">
        <tr>
            <td>
                <div class="lbl">Cliente</div>
                <div class="val name">{{ trim(($user->firstname ?? '').' '.($user->lastname ?? '')) ?: 'Cliente' }}</div>
                @if($user?->identification)<div class="val">{{ $user->identification_type }} {{ $user->identification }}</div>@endif
                <div class="val">{{ $user->email ?? '' }}</div>
            </td>
            <td>
                <div class="lbl">Fecha de pago</div>
                <div class="val">{{ \Carbon\Carbon::parse($order->payment_at ?? $order->created_at)->locale('es')->translatedFormat('j \\d\\e F \\d\\e Y, g:i a') }}</div>
                <div class="lbl">Método de pago</div>
                <div class="val">{{ optional($order->method)->title ?? '—' }}</div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th class="c-product">Producto</th>
                <th class="r c-qty">Cant.</th>
                <th class="r c-price">Precio</th>
                <th class="r c-subtotal">Subtotal</th>
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
            <tr><td>Precio de lista</td><td class="r">$ {{ number_format($order->total_before_discount, 0, ',', '.') }}</td></tr>
            <tr><td>Descuento{{ optional($order->coupon)->code ? ' ('.$order->coupon->code.')' : '' }}</td><td class="r">− $ {{ number_format($order->total_discount_amount, 0, ',', '.') }}</td></tr>
        @endif
        <tr class="grand"><td>Total pagado</td><td class="r">$ {{ number_format($order->total_order_amount, 0, ',', '.') }} COP</td></tr>
    </table>

    <div class="foot">
        Este documento es un comprobante de compra generado por {{ $brand }}.
    </div>

</div>
</body>
</html>
