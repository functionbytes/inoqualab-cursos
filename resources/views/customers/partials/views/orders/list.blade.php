{{-- Contenido de Mis pedidos: filtro por estado, buscador, tabla y
     paginador. Vive en un partial aparte para poder re-renderizarlo por AJAX
     (ver el script en orders/index.blade.php) sin recargar la página. --}}
@if($orders->isEmpty())

    {{-- El filtro debe verse incluso cuando el estado elegido no tiene
         resultados, para poder volver a "Todos" o cambiar de pestaña. --}}
    @if($totalOrdersCount > 0)
        <div class="pnl-filter" role="group" aria-label="Filtrar mis pedidos">
            <a href="{{ route('customers.orders', array_filter(['search' => $searchKey])) }}"
               class="{{ $condition ? '' : 'active' }}">
                Todos<span class="cnt">{{ $totalOrdersCount }}</span>
            </a>
            @foreach($conditions as $c)
                <a href="{{ route('customers.orders', array_filter(['search' => $searchKey, 'condition' => $c->id])) }}"
                   class="{{ (string) $condition === (string) $c->id ? 'active' : '' }}">
                    {{ $c->title }}<span class="cnt">{{ $conditionCounts->get($c->id, 0) }}</span>
                </a>
            @endforeach
        </div>
        <div class="pnl-gap"></div>
    @endif

    <div class="pnl-card pnl-head pnl-head-row">
        <div>
            {{-- El título ya lo muestra la banda de contexto del header. --}}
            <div class="sub">Historial de compras y facturas de tus capacitaciones</div>
        </div>
        <form class="pnl-search" action="{{ route('customers.orders') }}" method="GET" role="search">
            @include('customers.includes.icon', ['name' => 'search'])
            {{-- Un form GET descarta el query string del "action" -- sin este
                 hidden, buscar desde una pestaña de estado filtrada perdía
                 ese filtro (volvía siempre a "Todos"). --}}
            @if($condition)<input type="hidden" name="condition" value="{{ $condition }}">@endif
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

    <div class="pnl-empty">
        <span class="ic">@include('customers.includes.icon', ['name' => 'receipt'])</span>
        @if($searchKey)
            <h3>Ningún pedido coincide con «{{ $searchKey }}»</h3>
            <p>Revisa el número o quita la búsqueda para ver todo tu historial.</p>
            <a href="{{ route('customers.orders') }}">Ver todos los pedidos</a>
        @elseif($condition)
            <h3>No tienes pedidos en este estado</h3>
            <p>Prueba con otra pestaña para ver el resto de tu historial.</p>
            <a href="{{ route('customers.orders') }}">Ver todos los pedidos</a>
        @else
            <h3>Todavía no tienes pedidos</h3>
            <p>Cuando compres una capacitación, aquí tendrás el pedido, su estado de pago y la factura.</p>
            <a href="{{ route('home') }}">Explorar el catálogo</a>
        @endif
    </div>

@else

    {{-- Filtro por estado, título, buscador, columnas, filas y el pie con
         el paginador viven en la misma caja -- el filtro quedaba como
         una tarjeta aparte flotando encima de la tabla, con espacio
         visible entre ambas. --}}
    <div class="od-table">
        @if($totalOrdersCount > 0)
            <div class="pnl-filter pnl-filter-flat" role="group" aria-label="Filtrar mis pedidos">
                <a href="{{ route('customers.orders', array_filter(['search' => $searchKey])) }}"
                   class="{{ $condition ? '' : 'active' }}">
                    Todos<span class="cnt">{{ $totalOrdersCount }}</span>
                </a>
                @foreach($conditions as $c)
                    <a href="{{ route('customers.orders', array_filter(['search' => $searchKey, 'condition' => $c->id])) }}"
                       class="{{ (string) $condition === (string) $c->id ? 'active' : '' }}">
                        {{ $c->title }}<span class="cnt">{{ $conditionCounts->get($c->id, 0) }}</span>
                    </a>
                @endforeach
            </div>
        @endif

        <div class="od-head">
            <div class="sub">Historial de compras y facturas de tus capacitaciones</div>
            <form class="pnl-search" action="{{ route('customers.orders') }}" method="GET" role="search">
                @include('customers.includes.icon', ['name' => 'search'])
                @if($condition)<input type="hidden" name="condition" value="{{ $condition }}">@endif
                <input type="search" name="search" placeholder="Nº de pedido…" autocomplete="off"
                       value="{{ $searchKey ?? '' }}" aria-label="Buscar por número de pedido">
            </form>
        </div>

        @if($searchKey)
            <div class="od-searching od-searching-inline">
                Resultados para <b>{{ $searchKey }}</b>
                <a href="{{ route('customers.orders') }}">Quitar búsqueda</a>
            </div>
        @endif

        <div class="od-cols">
            <span>Pedido</span>
            <span>Contenido</span>
            <span>Total</span>
            <span>Estado</span>
            <span></span>
        </div>

        @foreach($orders as $order)
            @php
                // El importe se toma de total_order_amount cuando existe, como
                // hacía la tabla anterior; total es el respaldo de los pedidos
                // antiguos. Inline (no un closure externo): este partial también
                // se renderiza solo, desde la respuesta AJAX del controller.
                $monto = (float) ($order->total_order_amount ?? $order->total ?? 0);
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
            <span>Mostrando {{ $orders->firstItem() }}-{{ $orders->lastItem() }} de {{ $orders->total() }} resultados</span>
            <nav>{{ $orders->appends(request()->input())->links() }}</nav>
        </div>
    </div>

@endif
