@extends('layouts.customers')

@section('title', 'Compras y facturación')

@section('context-title', 'Compras y facturación')
@section('context-icon')@include('customers.includes.icon', ['name' => 'receipt'])@endsection
@section('context-subtitle', 'Revisa tus compras, facturas y su estado de pago')
@section('context-stat-number', $orders->total())
@section('context-stat-label', Str::plural('pedido', $orders->total()))

@php
    $importe = fn ($order) => (float) ($order->total_order_amount ?? $order->total ?? 0);

    // Los pedidos llegan ya ordenados por fecha desc; agruparlos por mes evita
    // una lista plana de números de pedido sin contexto temporal.
    $porMes = $orders->getCollection()->groupBy(function ($order) {
        return \Carbon\Carbon::parse($order->updated_at)->format('Y-m');
    });

    $gastado = $orders->getCollection()->sum($importe);
    $pendientes = $orders->getCollection()->filter(
        fn ($o) => in_array($o->condition_id, [1, 2], true) && $importe($o) > 0
    );
@endphp

@section('content')
<section class="pnl-section">

    <div class="cd-head">
        {{-- El título ya lo muestra la banda de contexto del header. --}}
        <div class="sub">Cada compra agrupa las capacitaciones que adquiriste y su comprobante</div>
    </div>

    @if($orders->isEmpty())

        <div class="pnl-empty">
            <span class="ic">@include('customers.includes.icon', ['name' => 'receipt'])</span>
            @if($searchKey)
                <h3>Ningún pedido coincide con «{{ $searchKey }}»</h3>
                <p>Revisa el número o quita la búsqueda para ver todo tu historial.</p>
                <a href="{{ route('customers.orders') }}">Ver todos los pedidos</a>
            @else
                <h3>Todavía no tienes compras</h3>
                <p>Cuando adquieras una capacitación, aquí tendrás el pedido, su estado de pago y la factura.</p>
                <a href="{{ route('home') }}">Explorar el catálogo</a>
            @endif
        </div>

    @else

    <div class="ob-wrap">

        <div class="ob-main">
            @foreach($porMes as $mes => $grupo)
                <div class="ob-month">{{ \Illuminate\Support\Str::ucfirst(\Carbon\Carbon::parse($mes.'-01')->locale('es')->isoFormat('MMMM [de] YYYY')) }}</div>

                @foreach($grupo as $order)
                    @php
                        $monto = $importe($order);
                        $puedePagar = in_array($order->condition_id, [1, 2], true) && $monto > 0;
                        $items = $order->items;
                    @endphp

                    <article class="ob-card">
                        <header>
                            <span class="ic cnd-{{ optional($order->condition)->slug }}">
                                @include('customers.includes.icon', ['name' => $puedePagar ? 'clock' : 'check'])
                            </span>
                            <div class="txt">
                                <b>Pedido {{ $order->slack }}</b>
                                <span>
                                    {{ \Carbon\Carbon::parse($order->updated_at)->locale('es')->isoFormat('D MMM YYYY') }} ·
                                    {{ optional($order->condition)->title ?? 'Sin estado' }} ·
                                    {{ $items->count() }} {{ $items->count() === 1 ? 'artículo' : 'artículos' }}
                                </span>
                            </div>
                            <div class="amount">
                                <b>$ {{ number_format($monto, 0, ',', '.') }}</b>
                                <span>IVA incluido</span>
                            </div>
                        </header>

                        @if($items->count() > 0)
                            <div class="ob-items">
                                @foreach($items as $item)
                                    @php $itemable = $item->itemable; @endphp
                                    <div class="line">
                                        <span class="tag">{{ $item->item_type && str_contains($item->item_type, 'Bundle') ? 'Paquete' : 'Curso' }}</span>
                                        <div class="name">{{ optional($itemable)->title ?? 'Artículo del pedido' }}</div>
                                        <div class="price">$ {{ number_format((float) ($item->price ?? 0), 0, ',', '.') }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <footer>
                            <a href="{{ route('customers.orders.view', $order->slack) }}">
                                @include('customers.includes.icon', ['name' => 'search']) Ver detalle del pedido
                            </a>
                            <a href="{{ route('customers.orders.invoice', $order->slack) }}" target="_blank">
                                @include('customers.includes.icon', ['name' => 'download']) Descargar factura
                            </a>
                            @if($puedePagar)
                                <a class="pay" href="{{ route('customers.orders.payments', $order->slack) }}">Pagar ahora</a>
                            @endif
                        </footer>
                    </article>
                @endforeach
            @endforeach

            <div class="od-foot bare">
                <span>Mostrando {{ $orders->firstItem() }}-{{ $orders->lastItem() }} de {{ $orders->total() }} resultados</span>
                <nav>{{ $orders->appends(request()->input())->links() }}</nav>
            </div>
        </div>

        <aside class="ob-side">
            <div class="ob-panel">
                <div class="eyebrow">En esta página</div>
                <div class="big">$ {{ number_format($gastado, 0, ',', '.') }}</div>
                <div class="figs">
                    <div><b>{{ $orders->total() }}</b><span>Pedidos</span></div>
                    <div><b class="{{ $pendientes->count() > 0 ? 'warn' : '' }}">{{ $pendientes->count() }}</b><span>Por pagar</span></div>
                </div>
            </div>

            @if($pendientes->count() > 0)
                <div class="cx-warn">
                    <div class="top">
                        <span class="ic">@include('customers.includes.icon', ['name' => 'warning'])</span>
                        <h4>{{ $pendientes->count() }} {{ $pendientes->count() === 1 ? 'pedido pendiente' : 'pedidos pendientes' }}</h4>
                    </div>
                    <p>El acceso al curso se habilita en cuanto se confirma el pago.</p>
                    <a class="cx-warn-link" href="{{ route('customers.orders.payments', $pendientes->first()->slack) }}">Pagar el más antiguo</a>
                </div>
            @endif

            <div class="ob-billing">
                <div class="head">
                    <h4>Datos de facturación</h4>
                    <a href="{{ route('customers.settings') }}">Editar</a>
                </div>
                <div class="field">
                    <span>Titular</span>
                    <b>{{ auth()->user()->firstname }} {{ auth()->user()->lastname }}</b>
                </div>
                <div class="field">
                    <span>Identificación</span>
                    <b class="{{ auth()->user()->identification ? '' : 'missing' }}">
                        {{ auth()->user()->identification ?: 'Sin registrar' }}
                    </b>
                </div>
                <div class="field">
                    <span>Correo</span>
                    <b>{{ auth()->user()->email }}</b>
                </div>
            </div>
        </aside>

    </div>

    @endif

</section>
@endsection
