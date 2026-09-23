<!-- resources/views/distributors/views/orders/orders/print.blade.php -->
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
        body { font-family: 'Plus Jakarta Sans', sans-serif; color: #1b2a3a; font-size: 12px; margin: 0; }
        .accent { background: #008bce; height: 6px; width: 100%; }
        .wrap { padding: 28px 34px; }
        .head { width: 100%; border-bottom: 2px solid #0d1b2a; padding-bottom: 14px; margin-bottom: 22px; }
        .head td { vertical-align: middle; }
        .brand-logo { height: 40px; width: auto; }
        .brand { font-size: 20px; font-weight: bold; color: #0d1b2a; }
        .doc { text-align: right; }
        .doc .t { font-size: 16px; font-weight: bold; color: #006fa3; }
        .doc .ref { color: #6a7888; font-size: 11px; }
        .meta { width: 100%; margin-bottom: 20px; }
        .meta td { vertical-align: top; padding-right: 16px; width: 50%; }
        .meta .sec { color: #0d1b2a; font-size: 13px; font-weight: bold; margin-bottom: 8px; }
        .meta .lbl { color: #8d9db5; font-size: 10px; text-transform: uppercase; letter-spacing: .04em; }
        .meta .val { font-size: 12px; color: #1b2a3a; margin-bottom: 6px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 18px; table-layout: fixed; }
        table.items th { background: #0d1b2a; color: #fff; text-align: left; padding: 8px 10px; font-size: 11px; }
        table.items td { padding: 8px 10px; border-bottom: 1px solid #e7ecf1; }
        table.items td.r, table.items th.r { text-align: right; white-space: nowrap; }
        table.items th.c-product { width: auto; }
        table.items th.c-qty, table.items th.c-subtotal { width: 90px; }
        .totals { width: 45%; margin-left: 55%; }
        .totals td { padding: 5px 10px; white-space: nowrap; }
        .totals td.r { text-align: right; }
        .totals .grand td { border-top: 2px solid #0d1b2a; font-size: 15px; font-weight: bold; color: #006fa3; padding-top: 8px; }
        .foot { margin-top: 30px; color: #8d9db5; font-size: 10.5px; text-align: center; border-top: 1px solid #e7ecf1; padding-top: 14px; }
    </style>
</head>
<body>
<div class="accent"></div>
<div class="wrap">

    <table class="head">
        <tr>
            <td>
                @if($logo)
                    <img class="brand-logo" src="{{ $logo }}" alt="{{ $brand }}">
                @else
                    <span class="brand">{{ $brand }}</span>
                @endif
            </td>
            <td class="doc">
                <div class="t">Detalle de orden</div>
                <div class="ref">Ref. {{ Str::upper($order->reference) }}</div>
                <div class="ref">{{ date('Y-m-d', strtotime($order->created_at)) }}</div>
            </td>
        </tr>
    </table>

    <table class="meta">
        <tr>
            <td>
                <div class="sec">Para</div>
                <div class="lbl">Cliente</div>
                <div class="val">{{ Str::upper(Str::lower(($order->user->firstname ?? 'N/D'))) }} {{ Str::upper(Str::lower(($order->user->lastname ?? ''))) }}</div>
                <div class="lbl">Identificación</div>
                <div class="val">{{ Str::upper(Str::lower(($order->user->identification ?? 'N/D'))) }}</div>
                @if($order->user->address ?? null)
                    <div class="lbl">Dirección</div>
                    <div class="val">{{ Str::upper(Str::lower($order->user->address)) }}</div>
                @endif
                @if($order->user->cellphone ?? null)
                    <div class="lbl">Celular</div>
                    <div class="val">{{ Str::upper(Str::lower($order->user->cellphone)) }}</div>
                @endif
            </td>

            <td>
                <div class="sec">Distribuidor</div>
                @if($order->activity != null)
                    <div class="lbl">Distribuidor</div>
                    <div class="val">{{ Str::upper($order->activity->distributor->title) }}</div>
                    <div class="lbl">Empresa</div>
                    <div class="val">{{ Str::upper($order->activity->enterprise->title) }}</div>
                    @if($order->activity->staff != null)
                        <div class="lbl">Encargado</div>
                        <div class="val">{{ Str::upper($order->activity->staff->firstname) }} {{ Str::upper($order->activity->staff->lastname) }}</div>
                    @endif
                @endif
                <div class="lbl">Tipo de pago</div>
                <div class="val">{{ Str::upper($order->type->title) }}</div>
                <div class="lbl">Método de pago</div>
                <div class="val">{{ Str::upper($order->method->title) }}</div>
                @if($order->payment_at != null)
                    <div class="lbl">Fecha de pago</div>
                    <div class="val">{{ date('Y-m-d', strtotime($order->payment_at)) }}</div>
                @endif
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th class="c-product">Descripción</th>
                <th class="r c-qty">Cantidad</th>
                <th class="r c-subtotal">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->itemable->title }}</td>
                    <td class="r">{{ ceil($item->quantity) }}</td>
                    <td class="r">${{ number_format($item->amount) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="r">${{ number_format($order->total_after_discount) }}</td></tr>
        @if($order->total_discount_amount > 0)
            <tr><td>Descuentos</td><td class="r">–${{ number_format($order->total_discount_amount) }}</td></tr>
        @endif
        @if($order->total_tax_amount > 0)
            <tr><td>Impuestos</td><td class="r">${{ number_format($order->total_tax_amount) }}</td></tr>
        @endif
        <tr class="grand"><td>Total</td><td class="r">${{ number_format($order->total_order_amount) }}</td></tr>
    </table>

    <div class="foot">
        Este documento es un comprobante de orden generado por {{ $brand }}.
    </div>

</div>
</body>
</html>
