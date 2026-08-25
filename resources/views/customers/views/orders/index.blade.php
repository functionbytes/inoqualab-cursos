@extends('layouts.customers')

@section('title', 'Mis pedidos')

@php
    $total = $orders->total();

    // El importe se toma de total_order_amount cuando existe, como hacía la
    // tabla anterior; total es el respaldo de los pedidos antiguos.
    $importe = fn ($order) => (float) ($order->total_order_amount ?? $order->total ?? 0);
@endphp

@section('content')
<section class="pnl-section">

    <div class="pnl-card pnl-head pnl-head-row">
        <div>
            <h2>Mis pedidos</h2>
            <div class="sub">Historial de compras y facturas de tus capacitaciones</div>
        </div>
        <form class="pnl-search" action="{{ Request::fullUrl() }}" method="GET" role="search">
            @include('customers.includes.icon', ['name' => 'search'])
            <input type="search" name="search" placeholder="Nº de pedido…" autocomplete="off"
                   value="{{ $searchKey ?? '' }}" aria-label="Buscar por número de pedido">
        </form>
    </div>

    <div class="pnl-gap"></div>

    @if($searchKey)
        <div class="od-searching">
            Resultados para <b>{{ $searchKey }}</b>
            <a href="{{ route('customers.orders') }}">Quitar búsqueda</a>
        </div>
    @endif

    @if($orders->isEmpty())

        <div class="pnl-empty">
            <span class="ic">@include('customers.includes.icon', ['name' => 'receipt'])</span>
            @if($searchKey)
                <h3>Ningún pedido coincide con «{{ $searchKey }}»</h3>
                <p>Revisa el número o quita la búsqueda para ver todo tu historial.</p>
                <a href="{{ route('customers.orders') }}">Ver todos los pedidos</a>
            @else
                <h3>Todavía no tienes pedidos</h3>
                <p>Cuando compres una capacitación, aquí tendrás el pedido, su estado de pago y la factura.</p>
                <a href="{{ route('home') }}">Explorar el catálogo</a>
            @endif
        </div>

    @else

        <div class="od-table">
            <div class="od-cols">
                <span>Pedido</span>
                <span>Contenido</span>
                <span>Total</span>
                <span>Estado</span>
                <span></span>
            </div>

            @foreach($orders as $order)
                @php
                    $monto = $importe($order);
                    $puedePagar = in_array($order->condition_id, [1, 2], true) && $monto > 0;
                    $items = $order->items;
                    $primero = $items->first();
                    $tituloItem = optional(optional($primero)->itemable)->title;
                @endphp

                <div class="od-row">
                    <div class="od-id">
                        <b>{{ $order->slack }}</b>
                        <span>{{ \Carbon\Carbon::parse($order->updated_at)->locale('es')->isoFormat('D MMM YYYY') }}</span>
                    </div>

                    <div class="od-items">
                        @if($tituloItem)
                            <b>{{ $tituloItem }}</b>
                            @if($items->count() > 1)
                                <span>y {{ $items->count() - 1 }} {{ $items->count() - 1 === 1 ? 'artículo más' : 'artículos más' }}</span>
                            @endif
                        @else
                            <span class="muted">Sin detalle de artículos</span>
                        @endif
                    </div>

                    <div class="od-total">$ {{ number_format($monto, 0, ',', '.') }}</div>

                    <div>
                        <span class="od-chip cnd-{{ optional($order->condition)->slug }}">
                            {{ optional($order->condition)->title ?? 'Sin estado' }}
                        </span>
                    </div>

                    {{-- Las acciones dejan de estar escondidas en un menú de tres
                         puntos: pagar era la más importante y costaba encontrarla. --}}
                    <div class="od-act">
                        <a class="ghost" href="{{ route('customers.orders.view', $order->slack) }}">Ver detalle</a>
                        @if($puedePagar)
                            <a class="solid" href="{{ route('customers.orders.payments', $order->slack) }}">Pagar</a>
                        @endif
                    </div>
                </div>
            @endforeach

            <div class="od-foot">
                <span>Mostrando {{ $orders->firstItem() }}-{{ $orders->lastItem() }} de {{ $total }} resultados</span>
                <nav>{{ $orders->appends(request()->input())->links() }}</nav>
            </div>
        </div>

    @endif

</section>
@endsection
