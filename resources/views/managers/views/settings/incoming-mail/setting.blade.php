@extends('layouts.managers')

@push('css')
<link rel="stylesheet" href="{{ url('managers/css/views/settings/incoming-mail/setting.css') }}?v={{ filemtime(public_path('managers/css/views/settings/incoming-mail/setting.css')) }}">
@endpush

@section('page_header')
    @include('managers.includes.card', ['title' => 'Correos entrantes'])
@endsection
@section('content')

@php
    $enabled          = setting('incoming_mail_enabled') !== '' ? setting('incoming_mail_enabled') === 'true' : config('incoming_mail.enabled');
    $autoProcess      = setting('incoming_mail_auto_process') !== '' ? setting('incoming_mail_auto_process') === 'true' : config('incoming_mail.auto_process');
    $threshold        = setting('incoming_mail_confidence_threshold') !== '' ? (int) setting('incoming_mail_confidence_threshold') : config('incoming_mail.confidence_threshold');
    $trustAll         = setting('incoming_mail_trust_all_senders') !== '' ? setting('incoming_mail_trust_all_senders') === 'true' : config('incoming_mail.trust_all_senders');

    $stats = [
        'processed'      => \App\Models\Mail\IncomingMail::where('status', 'processed')->count(),
        'pending_review' => \App\Models\Mail\IncomingMail::where('status', 'pending_review')->count(),
        'failed'         => \App\Models\Mail\IncomingMail::where('status', 'failed')->count(),
        'ignored'        => \App\Models\Mail\IncomingMail::where('status', 'ignored')->count(),
    ];
@endphp

<div class="row g-4 align-items-start">

    {{-- Columna izquierda: formulario --}}
    <div class="col-lg-8">
        <form id="formIncomingMail" data-update-url="{{ route('manager.settings.incoming-mail.update') }}">
            @csrf

            <div class="card">

                {{-- Habilitar servicio --}}
                <div class="card-header border-bottom">
                    <h6 class="mb-1 fw-bold">Servicio habilitado</h6>
                    <p class="text-muted small mb-0">
                        Activa o desactiva el polling IMAP por completo. Si está desactivado,
                        el comando <code>mail:fetch-orders</code> no hace nada aunque el scheduler lo ejecute.
                    </p>
                </div>

                <div class="card-body">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox"
                            name="incoming_mail_enabled" id="incoming_mail_enabled"
                            @checked($enabled) />
                        <label class="form-check-label fw-semibold" for="incoming_mail_enabled">Habilitar servicio</label>
                    </div>
                </div>

                <div id="incomingMailFields" class="{{ $enabled ? '' : 'd-none' }}">

                <hr class="my-0">

                {{-- Auto-proceso --}}
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-1">Procesamiento automático</h6>
                    <p class="text-muted mb-3">
                        Cuando está <strong>activado</strong>, los correos con confianza ≥ umbral se procesan
                        inmediatamente y crean la orden sin intervención humana.
                        Cuando está <strong>desactivado</strong>, <em>todos</em> los correos van a la
                        bandeja de revisión (en el panel de soporte) sin importar el nivel de confianza.
                    </p>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox"
                            name="incoming_mail_auto_process" id="incoming_mail_auto_process"
                            @checked($autoProcess) />
                        <label class="form-check-label fw-semibold" for="incoming_mail_auto_process">Procesamiento automático</label>
                    </div>
                </div>

                <hr class="my-0">

                {{-- Umbral de confianza --}}
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-1">Umbral de confianza</h6>
                    <p class="text-muted mb-3">
                        Puntuación mínima (0–100) para auto-procesar un correo. Por debajo de este valor
                        el correo pasa a revisión manual aunque el procesamiento automático esté activo.
                        Recomendado: 90 — garantiza que empresa y todos los cursos estén perfectamente mapeados.
                    </p>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="number" class="form-control" id="incoming_mail_confidence_threshold"
                                    name="incoming_mail_confidence_threshold"
                                    value="{{ $threshold }}" min="0" max="100" step="1">
                                <span class="input-group-text">/ 100</span>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-0">

                {{-- Modo pruebas --}}
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-1">
                        Modo de prueba
                        @if($trustAll)
                            <span class="badge bg-secondary-subtle ms-1">ACTIVO</span>
                        @endif
                    </h6>
                    <p class="text-muted mb-3">
                        Procesa correos de <strong>cualquier remitente</strong> usando el parser por defecto
                        (ignora la lista de remitentes de confianza).
                    </p>
                    <div class="alert alert-plain-gray py-2 mb-3 d-flex align-items-center gap-2">
                        <i class="fas fa-triangle-exclamation"></i>
                        <span>Solo para pruebas. Desactivar antes de pasar a producción.</span>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox"
                            name="incoming_mail_trust_all_senders" id="incoming_mail_trust_all_senders"
                            @checked($trustAll) />
                        <label class="form-check-label fw-semibold" for="incoming_mail_trust_all_senders">Modo de prueba</label>
                    </div>
                </div>

                </div>{{-- /#incomingMailFields --}}

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary w-100" id="btnSaveIncomingMail">
                        Guardar configuración
                    </button>
                </div>

            </div>
        </form>
    </div>

    {{-- Columna derecha: sidebar informativo --}}
    <div class="col-lg-4">

        <div class="card mb-3">
            <div class="card-header border-bottom">
                <h6 class="mb-0 fw-bold">Estadísticas</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="d-flex justify-content-between align-items-center mb-3">
                        <span class="d-flex align-items-center gap-2">
                            <span class="stat-icon-circle stat-icon-circle--processed"><i class="fas fa-check"></i></span>
                            Procesados
                        </span>
                        <strong>{{ $stats['processed'] }}</strong>
                    </li>
                    <li class="d-flex justify-content-between align-items-center mb-3">
                        <span class="d-flex align-items-center gap-2">
                            <span class="stat-icon-circle stat-icon-circle--pending"><i class="fas fa-clock"></i></span>
                            Pendientes revisión
                        </span>
                        <strong>{{ $stats['pending_review'] }}</strong>
                    </li>
                    <li class="d-flex justify-content-between align-items-center mb-3">
                        <span class="d-flex align-items-center gap-2">
                            <span class="stat-icon-circle stat-icon-circle--failed"><i class="fas fa-xmark"></i></span>
                            Fallidos
                        </span>
                        <strong>{{ $stats['failed'] }}</strong>
                    </li>
                    <li class="d-flex justify-content-between align-items-center">
                        <span class="d-flex align-items-center gap-2">
                            <span class="stat-icon-circle stat-icon-circle--ignored"><i class="fas fa-ban"></i></span>
                            Ignorados
                        </span>
                        <strong>{{ $stats['ignored'] }}</strong>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header border-bottom">
                <h6 class="mb-0 fw-bold">Sobre estos ajustes</h6>
            </div>
            <div class="card-body">
                <h6 class="fw-semibold mb-2">Reglas de confianza</h6>
                <p class="text-muted mb-3">Cada empresa puede tener su propia regla de auto-confirmación en <a href="{{ route('manager.mails') }}">Correos entrantes</a>, que baja el umbral necesario para auto-procesar sus correos sin revisión manual.</p>

                <hr class="my-3">

                <h6 class="fw-semibold mb-2">Correos fallidos</h6>
                <p class="text-muted mb-0">Tras corregir datos (empresa, curso, regla), usa <code>mails:reparse-failed</code> para reintentar los correos que quedaron en estado fallido.</p>
            </div>
        </div>

    </div>

</div>

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/settings/incoming-mail/setting.js') }}"></script>
@endpush
