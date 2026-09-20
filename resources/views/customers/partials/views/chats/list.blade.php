{{-- Contenido de la bandeja de notificaciones: cabecera con buscador,
     mensaje de vacío y lista agrupada por día. Vive en un partial aparte
     para poder re-renderizarlo por AJAX (ver el script en chats/index.blade.php)
     sin recargar la página, igual que Mis pedidos/Certificados/Mis cursos. --}}
@php
    // $notifications llega agrupado por fecha (Y-m-d) desde el controlador.
    $planas = collect($notifications)->flatten(1);
    $sinLeer = $planas->filter(fn ($n) => is_null($n->read_at))->count();
@endphp

<div class="pnl-card pnl-head pnl-head-row">
    <div>
        {{-- El título ya lo muestra la banda de contexto del header (ver
             chats/index.blade.php) -- acá solo queda el detalle dinámico. --}}
        <div class="sub">
            @if($planas->count() > 0)
                {{ $sinLeer }} sin leer de {{ $planas->count() }}
            @else
                Aquí llegan los avisos sobre tus cursos, pedidos y certificados
            @endif
        </div>
    </div>
    {{-- Buscador y "marcar todas" agrupados: .pnl-head-row reparte el espacio
         entre 2 hijos (título y este grupo), no entre 3 -- con 3 el buscador
         quedaba flotando a la mitad en vez de pegado a la derecha. --}}
    <div class="pnl-head-tools">
        <form class="pnl-search" action="{{ route('customers.notifications') }}" method="GET" role="search">
            @include('customers.includes.icon', ['name' => 'search'])
            <input type="search" name="search" placeholder="Buscar notificación…" autocomplete="off"
                   value="{{ $searchKey ?? '' }}" aria-label="Buscar notificación">
        </form>
        @if($sinLeer > 0)
            <button type="button" class="nt-readall" id="ntReadAll">Marcar todas como leídas</button>
        @endif
    </div>
</div>

<div class="pnl-gap"></div>

@if($searchKey)
    <div class="od-searching">
        Resultados para <b>{{ $searchKey }}</b>
        <a href="{{ route('customers.notifications') }}">Quitar búsqueda</a>
    </div>
    <div class="pnl-gap"></div>
@endif

@if($planas->isEmpty())

    <div class="pnl-empty">
        <span class="ic">@include('customers.includes.icon', ['name' => 'bell'])</span>
        @if($searchKey)
            <h3>Ninguna notificación coincide con «{{ $searchKey }}»</h3>
            <p>Revisa el término o quita la búsqueda para ver toda tu bandeja.</p>
            <a href="{{ route('customers.notifications') }}">Ver todas las notificaciones</a>
        @else
            <h3>No tienes notificaciones</h3>
            <p>Te avisaremos aquí cuando haya novedades en tus cursos, cuando venza un acceso o cuando se emita un certificado.</p>
            <a href="{{ route('customers.courses') }}">Ir a mis cursos</a>
        @endif
    </div>

@else

    @foreach($notifications as $date => $group)
        @php
            $dia = \Carbon\Carbon::parse($date);
            if ($dia->isToday()) {
                $titulo = 'Hoy';
            } elseif ($dia->isYesterday()) {
                $titulo = 'Ayer';
            } else {
                $titulo = \Illuminate\Support\Str::ucfirst($dia->locale('es')->isoFormat('D [de] MMMM [de] YYYY'));
            }
        @endphp

        <div class="nt-day">{{ $titulo }}</div>

        <div class="nt-group">
            @foreach($group as $notification)
                @php $nueva = is_null($notification->read_at); @endphp

                <div class="nt-item {{ $nueva ? 'is-new' : '' }} notification-item" data-id="{{ $notification->id }}">
                    <span class="ic">@include('customers.includes.icon', ['name' => 'bell'])</span>

                    <div class="txt">
                        <div class="top">
                            <b>{{ $notification->data['title'] ?? 'Notificación' }}</b>
                            @if($nueva)<span class="dot unread-badge"></span>@endif
                        </div>
                        <p>{{ $notification->data['message'] ?? '' }}</p>
                        @if(! empty($notification->data['link']))
                            <a class="go" href="{{ $notification->data['link'] }}">Ver detalle</a>
                        @endif
                    </div>

                    <div class="side">
                        <span class="time">{{ $notification->created_at->diffForHumans() }}</span>
                        <div class="nt-actions">
                            @if($nueva)
                                <button type="button" class="btn-mark-read" data-id="{{ $notification->id }}">Marcar leída</button>
                            @endif
                            <button type="button" class="nt-del" data-id="{{ $notification->id }}" aria-label="Eliminar notificación" title="Eliminar">
                                @include('customers.includes.icon', ['name' => 'x'])
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach

@endif
