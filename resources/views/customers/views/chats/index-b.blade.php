@extends('layouts.customers')

@section('title', 'Bandeja')

@php
    // El controlador agrupa por fecha; la bandeja necesita la lista plana en
    // orden descendente para el panel de selección.
    $planas = collect($notifications)->flatten(1)->values();
    $sinLeer = $planas->filter(fn ($n) => is_null($n->read_at))->count();
    $primera = $planas->first();
@endphp

@section('context-title', 'Bandeja')
@section('context-icon')@include('customers.includes.icon', ['name' => 'bell'])@endsection
@section('context-subtitle', 'Avisos sobre tus cursos, pedidos y certificados')
@section('context-stat-number', $planas->count())
{{-- Str::plural('notificación', ...) da "notificacións" (reglas en inglés) --}}
@section('context-stat-label', $planas->count() === 1 ? 'notificación' : 'notificaciones')

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

    <div class="nb-shell"
         data-mark-url="{{ route('customers.notifications.mark') }}">

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
<script src="{{ asset('customers/js/views/chats/index-b.js') }}"></script>
@endpush
