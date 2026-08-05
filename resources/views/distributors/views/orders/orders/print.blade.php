<!-- resources/views/distributors/views/orders/orders/print.blade.php -->
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
        }

        /* Container */
        .container {
            padding: 20px;
        }

        /* Two-column layout using tables */
        .table-layout {
            width: 100%;
            border-spacing: 0;
            margin-bottom: 20px;
        }

        .table-layout td {
            padding: 10px;
            vertical-align: top;
        }

        .col-half {
            width: 50%;
        }

        .col-full {
            width: 100%;
        }

        .fw-semibold {
            font-weight: 600;
        }

        h4, h5, h6 {
            margin-bottom: 10px;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f7f7f7;
            font-weight: bold;
        }

        /* Invoice header and section */
        .invoice-header {
            font-size: 1.2em;
            font-weight: bold;
        }

        .invoice-summary {
            margin-top: 20px;
            border-top: 2px solid #ccc;
            padding-top: 10px;
        }

        .invoice-summary h6 {
            font-weight: bold;
        }

        /* Responsive Design */
        .text-uppercase {
            text-transform: uppercase;
        }

        .d-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .d-flex p, .d-flex h6 {
            margin: 0;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Two Column Layout with Table -->
    <table class="table-layout">
        <tr>
            <!-- Customer Info Column -->
            <td class="col-half">
                <h4 class="fw-semibold">Para</h4>
                <p><strong>Cliente :</strong> {{ Str::upper(Str::lower(($order->user->firstname ?? 'N/D'))) }} {{ Str::upper(Str::lower(($order->user->lastname ?? ''))) }}</p>
                <p><strong>Indentificación :</strong> {{ Str::upper(Str::lower(($order->user->identification ?? 'N/D'))) }}</p>
                <p class="{{ ($order->user->address ?? null) !=null ? '' : 'd-none' }}"><strong>Dirección :</strong> {{ Str::upper(Str::lower(($order->user->address ?? ''))) }}</p>
                <p class="{{ ($order->user->cellphone ?? null) !=null ? '' : 'd-none' }}"><strong>Celular :</strong> {{ Str::upper(Str::lower(($order->user->cellphone ?? ''))) }}</p>
            </td>

            <!-- Distributor Info Column -->
            <td class="col-half">
                <h4 class="fw-semibold">Distribuidor</h4>
                @if($order->activity != null)
                    <p><strong>Distribuidor :</strong> {{ Str::upper($order->activity->distributor->title) }}</p>
                    <p><strong>Empresa :</strong> {{ Str::upper($order->activity->enterprise->title) }}</p>
                    @if($order->activity->staff != null)
                        <p><strong>Encargado :</strong> {{ Str::upper($order->activity->staff->firstname) }} {{ Str::upper($order->activity->staff->lastname) }}</p>
                    @endif
                @endif
                <p><strong>Referencia :</strong> {{ Str::upper($order->reference) }}</p>
                <p><strong>Tipo de pago :</strong> {{ Str::upper($order->type->title) }}</p>
                <p><strong>Metodo de pago :</strong> {{ Str::upper($order->method->title) }}</p>
                <p><strong>Fecha de creación :</strong> {{ date('Y-m-d', strtotime($order->created_at)) }}</p>
                @if($order->payment_at != null)
                    <p><strong>Fecha de pago :</strong> {{ date('Y-m-d', strtotime($order->payment_at)) }}</p>
                @endif
            </td>
        </tr>
    </table>

    <!-- Itemized Order Details -->
    <table>
        <thead>
        <tr>
            <th class="text-uppercase">Descripción</th>
            <th class="text-uppercase">Cantidad</th>
            <th class="text-uppercase">Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($order->items as $item)
            <tr>
                <td><strong>{{ $item->itemable->title }}</strong></td>
                <td>{{ ceil($item->quantity) }}</td>
                <td>${{ number_format($item->amount) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <!-- Order Summary -->
    <div class="invoice-summary">
        <div class="d-flex">
            <p><strong>Subtotal</strong></p>
            <h6>${{ number_format($order->total_after_discount) }}</h6>
        </div>

        @if($order->total_discount_amount > 0)
            <div class="d-flex">
                <p><strong>Descuentos</strong></p>
                <h6>${{ number_format($order->total_discount_amount) }}</h6>
            </div>
        @endif

        @if($order->total_tax_amount > 0)
            <div class="d-flex">
                <p><strong>Impuestos</strong></p>
                <h6>${{ number_format($order->total_tax_amount) }}</h6>
            </div>
        @endif

        <div class="d-flex">
            <p><strong>Total</strong></p>
            <h6>${{ number_format($order->total_order_amount) }}</h6>
        </div>
    </div>
</div>
</body>
</html>
