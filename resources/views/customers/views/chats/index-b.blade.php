@extends('layouts.customers')

@section('title', 'Bandeja')

@php
    // El controlador agrupa por fecha; la bandeja necesita la lista plana en
    // orden descendente para el panel de selección.
    $planas = collect($notifications)->flatten(1)->values();
    $sinLeer = $planas->filter(fn ($n) => is_null($n->read_at))->count();
    $primera = $planas->first();
@endphp

@section('content')
<section class="pnl-section">

    @if($planas->isEmpty())

        <div class="cd-head">
            <h2>Bandeja</h2>
            <div class="sub">Avisos sobre tus cursos, pedidos y certificados</div>
        </div>

        <div class="pnl-empty">
            <span class="ic">@include('customers.includes.icon', ['name' => 'bell'])</span>
            <h3>Tu bandeja está vacía</h3>
            <p>Te avisaremos aquí cuando haya novedades en tus cursos, cuando venza un acceso o cuando se emita un certificado.</p>
            <a href="{{ route('customers.courses') }}">Ir a mis cursos</a>
        </div>

    @else

    <div class="nb-shell">

        <div class="nb-list">
            <div class="nb-list-head">
                <div class="nb-title">
                    <h2>Bandeja</h2>
                    @if($sinLeer > 0)
                        <span class="pill">{{ $sinLeer }} {{ $sinLeer === 1 ? 'nueva' : 'nuevas' }}</span>
                    @endif
                </div>
                @if($sinLeer > 0)
                    <button type="button" class="nt-readall" id="ntReadAll">Marcar todas como leídas</button>
                @endif
            </div>

            <div class="nb-rows">
                @foreach($planas as $i => $notification)
                    @php $nueva = is_null($notification->read_at); @endphp

                    <button type="button"
                            class="nb-row notification-item {{ $nueva ? 'is-new' : '' }} {{ $i === 0 ? 'is-active' : '' }}"
                            data-id="{{ $notification->id }}"
                            data-title="{{ $notification->data['title'] ?? 'Notificación' }}"
                            data-message="{{ $notification->data['message'] ?? '' }}"
                            data-date="{{ \Illuminate\Support\Str::ucfirst($notification->created_at->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY · HH:mm')) }}"
                            data-link="{{ $notification->data['link'] ?? '' }}">
                        <span class="ic">@include('customers.includes.icon', ['name' => 'bell'])</span>
                        <span class="txt">
                            <span class="top">
                                <b>{{ $notification->data['title'] ?? 'Notificación' }}</b>
                                @if($nueva)<span class="dot unread-badge"></span>@endif
                            </span>
                            <span class="prev">{{ \Illuminate\Support\Str::limit($notification->data['message'] ?? '', 64) }}</span>
                        </span>
                        <span class="when">{{ $notification->created_at->diffForHumans(null, true) }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="nb-detail">
            <div class="nb-meta">
                <span class="tag">Aviso</span>
                <span class="date" id="nbDate">—</span>
            </div>
            <h2 id="nbTitle">{{ optional($primera)->data['title'] ?? 'Notificación' }}</h2>
            <p id="nbMessage">{{ optional($primera)->data['message'] ?? '' }}</p>

            <div class="nb-actions">
                <a class="solid" id="nbLink" href="#">Ver detalle</a>
                <button type="button" class="ghost" id="nbMarkRead">Marcar como leída</button>
            </div>

            <div class="nb-foot">
                @include('customers.includes.icon', ['name' => 'gear'])
                <span>Puedes elegir qué avisos recibir por correo en <a href="{{ route('customers.settings') }}">Configuración</a>.</span>
            </div>
        </div>

    </div>

    @endif

</section>
@endsection

@push('scripts')
<script>
$(function () {
    var seleccionada = null;

    function pintar($row) {
        seleccionada = $row;

        $('.nb-row').removeClass('is-active');
        $row.addClass('is-active');

        $('#nbTitle').text($row.data('title'));
        $('#nbMessage').text($row.data('message'));
        $('#nbDate').text($row.data('date'));

        var link = $row.data('link');
        $('#nbLink').toggle(!! link).attr('href', link || '#');
        $('#nbMarkRead').toggle($row.hasClass('is-new'));
    }

    function marcar(id, $item, cb) {
        $.ajax({
            url: '{{ route('customers.notifications.mark') }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { id: id },
            success: function () {
                $item.removeClass('is-new');
                $item.find('.unread-badge').remove();
                if (cb) { cb(); }
            },
            error: function () {
                toastr.error('Error al marcar la notificación');
            }
        });
    }

    $('.nb-row').on('click', function () { pintar($(this)); });

    $('#nbMarkRead').on('click', function () {
        if (! seleccionada) { return; }

        marcar(seleccionada.data('id'), seleccionada, function () {
            $('#nbMarkRead').hide();
            toastr.success('Notificación marcada como leída');
        });
    });

    $('#ntReadAll').on('click', function () {
        var $pendientes = $('.nb-row.is-new');

        if (! $pendientes.length) { return; }

        $pendientes.each(function () {
            var $item = $(this);
            marcar($item.data('id'), $item);
        });

        $('#nbMarkRead').hide();
        $(this).remove();
        $('.nb-list-head .pill').remove();
        toastr.success('Notificaciones marcadas como leídas');
    });

    // Estado inicial: la primera notificación de la lista.
    var $primera = $('.nb-row').first();
    if ($primera.length) { pintar($primera); }
});
</script>
@endpush
