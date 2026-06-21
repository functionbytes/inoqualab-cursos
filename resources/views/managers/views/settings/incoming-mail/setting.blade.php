@extends('layouts.managers')

@section('content')

@php
    $enabled          = setting('incoming_mail_enabled') !== '' ? setting('incoming_mail_enabled') === 'true' : config('incoming_mail.enabled');
    $autoProcess      = setting('incoming_mail_auto_process') !== '' ? setting('incoming_mail_auto_process') === 'true' : config('incoming_mail.auto_process');
    $threshold        = setting('incoming_mail_confidence_threshold') !== '' ? (int) setting('incoming_mail_confidence_threshold') : config('incoming_mail.confidence_threshold');
    $trustAll         = setting('incoming_mail_trust_all_senders') !== '' ? setting('incoming_mail_trust_all_senders') === 'true' : config('incoming_mail.trust_all_senders');
@endphp

<div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">
        <div class="card w-100">

            <form id="formIncomingMail" onsubmit="return false">
                @csrf

                {{-- Habilitar servicio --}}
                <div class="card-body border-top">
                    <div class="row align-items-center">
                        <div class="col-sm-11">
                            <label class="control-label col-form-label fw-semibold">Servicio habilitado</label>
                            <p class="card-subtitle mb-0 mt-1">
                                Activa o desactiva el polling IMAP por completo. Si está desactivado,
                                el comando <code>mail:fetch-orders</code> no hace nada aunque el scheduler lo ejecute.
                            </p>
                        </div>
                        <div class="col-sm-1 justify-content-end d-flex">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"
                                    name="incoming_mail_enabled" id="incoming_mail_enabled"
                                    @checked($enabled) />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Auto-proceso --}}
                <div class="card-body border-top">
                    <div class="row align-items-center">
                        <div class="col-sm-11">
                            <label class="control-label col-form-label fw-semibold">Procesamiento automático</label>
                            <p class="card-subtitle mb-0 mt-1">
                                Cuando está <strong>activado</strong>, los correos con confianza ≥ umbral se procesan
                                inmediatamente y crean la orden sin intervención humana.<br>
                                Cuando está <strong>desactivado</strong>, <em>todos</em> los correos van a la
                                <a href="{{ route('support.mails.index') }}" target="_blank">bandeja de revisión</a>
                                sin importar el nivel de confianza.
                            </p>
                        </div>
                        <div class="col-sm-1 justify-content-end d-flex">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"
                                    name="incoming_mail_auto_process" id="incoming_mail_auto_process"
                                    @checked($autoProcess) />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Umbral de confianza --}}
                <div class="card-body border-top">
                    <div class="d-flex no-block align-items-center mb-2">
                        <h5 class="mb-0">Umbral de confianza</h5>
                    </div>
                    <p class="card-subtitle mb-3 mt-1">
                        Puntuación mínima (0–100) para auto-procesar un correo. Por debajo de este valor
                        el correo pasa a revisión manual aunque el procesamiento automático esté activo.
                        <br><p class="text-muted">Recomendado: 90 — garantiza que empresa y todos los cursos estén perfectamente mapeados.</p>
                    </p>
                    <div class="row">
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

                {{-- Modo pruebas --}}
                <div class="card-body border-top">
                    <div class="row align-items-center">
                        <div class="col-sm-11">
                            <label class="control-label col-form-label fw-semibold">
                                Modo de prueba
                                @if($trustAll)
                                    <span class="badge bg-danger ms-1">ACTIVO</span>
                                @endif
                            </label>
                            <p class="card-subtitle mb-0 mt-1">
                                Procesa correos de <strong>cualquier remitente</strong> usando el parser por defecto
                                (ignora la lista de remitentes de confianza).<br>
                                <strong class="text-danger">
                                    <i class="fas fa-triangle-exclamation me-1"></i>
                                    Solo para pruebas. Desactivar antes de pasar a producción.
                                </strong>
                            </p>
                        </div>
                        <div class="col-sm-1 justify-content-end d-flex">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"
                                    name="incoming_mail_trust_all_senders" id="incoming_mail_trust_all_senders"
                                    @checked($trustAll) />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Guardar --}}
                <div class="card-body">
                    <button type="submit" class="btn btn-primary" id="btnSaveIncomingMail">
                        <i class="fas fa-save me-1"></i> Guardar configuración
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- Stats rápidas --}}
<div class="row mt-3">
    @php
        $stats = [
            'processed'      => \App\Models\Mail\IncomingMail::where('status', 'processed')->count(),
            'pending_review' => \App\Models\Mail\IncomingMail::where('status', 'pending_review')->count(),
            'failed'         => \App\Models\Mail\IncomingMail::where('status', 'failed')->count(),
            'ignored'        => \App\Models\Mail\IncomingMail::where('status', 'ignored')->count(),
        ];
    @endphp

    <div class="col-md-3">
        <div class="card border-0 shadow-none bg-light-success">
            <div class="card-body py-3 px-3">
                <div class="d-flex align-items-center">
                    <div class="me-3"><i class="fas fa-check-circle fs-4 text-success"></i></div>
                    <div>
                        <div class="fs-5 fw-semibold">{{ $stats['processed'] }}</div>
                        <div class="text-muted small">Procesados</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-none bg-light-warning">
            <div class="card-body py-3 px-3">
                <div class="d-flex align-items-center">
                    <div class="me-3"><i class="fas fa-clock fs-4 text-warning"></i></div>
                    <div>
                        <div class="fs-5 fw-semibold">{{ $stats['pending_review'] }}</div>
                        <div class="text-muted small">Pendientes revisión</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-none bg-light-danger">
            <div class="card-body py-3 px-3">
                <div class="d-flex align-items-center">
                    <div class="me-3"><i class="fas fa-times-circle fs-4 text-danger"></i></div>
                    <div>
                        <div class="fs-5 fw-semibold">{{ $stats['failed'] }}</div>
                        <div class="text-muted small">Fallidos</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-none bg-light-secondary">
            <div class="card-body py-3 px-3">
                <div class="d-flex align-items-center">
                    <div class="me-3"><i class="fas fa-ban fs-4 text-secondary"></i></div>
                    <div>
                        <div class="fs-5 fw-semibold">{{ $stats['ignored'] }}</div>
                        <div class="text-muted small">Ignorados</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {

    $('#formIncomingMail').submit(function () {
        var btn = $('#btnSaveIncomingMail');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Guardando...');

        $.ajax({
            type: 'POST',
            url: '{{ route('manager.settings.incoming-mail.update') }}',
            data: $(this).serialize(),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                if (response.success) {
                    toastr.success(response.message);
                    // Refrescar para que el badge ACTIVO se actualice
                    setTimeout(function () { location.reload(); }, 800);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function () {
                toastr.error('Error al guardar la configuración');
            },
            complete: function () {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Guardar configuración');
            }
        });
    });

});
</script>
@endpush
