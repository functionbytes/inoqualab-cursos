@extends('layouts.customers')

@section('title', 'Notificaciones')

@php
    // $notifications llega agrupado por fecha (Y-m-d) desde el controlador.
    $planas = collect($notifications)->flatten(1);
    $sinLeer = $planas->filter(fn ($n) => is_null($n->read_at))->count();
@endphp

@section('content')
<section class="pnl-section">

    <div class="pnl-card pnl-head pnl-head-row">
        <div>
            <h2>Notificaciones</h2>
            <div class="sub">
                @if($planas->count() > 0)
                    {{ $sinLeer }} sin leer de {{ $planas->count() }}
                @else
                    Aquí llegan los avisos sobre tus cursos, pedidos y certificados
                @endif
            </div>
        </div>
        @if($sinLeer > 0)
            <button type="button" class="nt-readall" id="ntReadAll">Marcar todas como leídas</button>
        @endif
    </div>

    <div class="pnl-gap"></div>

    @if($planas->isEmpty())

        <div class="pnl-empty">
            <span class="ic">@include('customers.includes.icon', ['name' => 'bell'])</span>
            <h3>No tienes notificaciones</h3>
            <p>Te avisaremos aquí cuando haya novedades en tus cursos, cuando venza un acceso o cuando se emita un certificado.</p>
            <a href="{{ route('customers.courses') }}">Ir a mis cursos</a>
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
                            @if($nueva)
                                <button type="button" class="btn-mark-read" data-id="{{ $notification->id }}">Marcar leída</button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach

    @endif

</section>
@endsection

@push('scripts')
<script>
$(function () {
    function marcar(id, $item, cb) {
        $.ajax({
            url: '{{ route('customers.notifications.mark') }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { id: id },
            success: function () {
                $item.removeClass('is-new');
                $item.find('.unread-badge, .btn-mark-read').remove();
                if (cb) { cb(); }
            },
            error: function () {
                toastr.error('Error al marcar la notificación');
            }
        });
    }

    $(document).on('click', '.btn-mark-read', function () {
        var $btn = $(this);
        marcar($btn.data('id'), $btn.closest('.notification-item'), function () {
            toastr.success('Notificación marcada como leída');
        });
    });

    // Marcar todas: una petición por notificación sin leer, que es lo que
    // acepta la ruta actual; el botón desaparece cuando no queda ninguna.
    $('#ntReadAll').on('click', function () {
        var $pendientes = $('.notification-item.is-new');

        if (! $pendientes.length) { return; }

        $(this).prop('disabled', true);

        $pendientes.each(function () {
            var $item = $(this);
            marcar($item.data('id'), $item);
        });

        $(this).remove();
        toastr.success('Notificaciones marcadas como leídas');
    });
});
</script>
@endpush
