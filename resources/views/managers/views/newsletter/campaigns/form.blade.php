@extends('layouts.managers')

@section('title', $campaign ? 'Editar campaña' : 'Nueva campaña')

@section('content')

    <div class="row g-3">

        {{-- Columna principal --}}
        <div class="col-12 col-lg-8">
            <div class="card">

                {{-- Header --}}
                <div class="card-header border-bottom p-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="mb-0 fw-bold">
                                {{ $campaign ? 'Editar campaña' : 'Nueva campaña' }}
                            </h5>
                            <p class="text-muted">Edita el contenido del newsletter</p>
                        </div>
                        @if($campaign)
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0 small">
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('manager.newsletter.campaigns.index') }}" class="text-muted">Campañas</a>
                                    </li>
                                    <li class="breadcrumb-item active">
                                        {{ $campaign->name }}
                                    </li>
                                </ol>
                            </nav>
                        @endif
                    </div>
                </div>

                {{-- Campos meta --}}
                <div class="card-body border-bottom">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre interno <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="fieldName"
                               value="{{ old('name', $campaign?->name) }}"
                               placeholder="Ej: Newsletter junio 2026"
                               maxlength="255">
                        <div class="form-text">Solo visible en el panel, no se envía.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Asunto del email <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="fieldSubject"
                               value="{{ old('subject', $campaign?->subject) }}"
                               placeholder="Ej: Novedades de {SITE_NAME} — Junio"
                               maxlength="255">
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Preencabezado</label>
                        <input type="text" class="form-control" id="fieldPreheader"
                               value="{{ old('preheader', $campaign?->preheader) }}"
                               placeholder="Texto breve visible antes de abrir el email (opcional)"
                               maxlength="255">
                    </div>
                </div>

                {{-- Tabs: Código / Vista previa --}}
                <ul class="nav nav-tabs nav-fill border-bottom" id="editorTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="code-tab" data-bs-toggle="tab"
                                data-bs-target="#code-panel" type="button" role="tab">
                            <i class="fas fa-code me-1"></i>Código
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="preview-tab" data-bs-toggle="tab"
                                data-bs-target="#preview-panel" type="button" role="tab">
                            <i class="fas fa-eye me-1"></i>Vista previa
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="editorTabsContent">

                    {{-- Tab Código --}}
                    <div class="tab-pane fade show active p-0" id="code-panel" role="tabpanel">

                        <div id="codeEditorWrapper"></div>

                        {{-- Variables disponibles --}}
                        <div class="border-top p-3 bg-light">
                            <div class="d-flex justify-content-between align-items-center gap-3 mb-2">
                                <div>
                                    <h6 class="mb-0 fw-semibold text-dark">Variables disponibles</h6>
                                    <p class="text-muted">Haz clic en una variable para insertarla en el cursor</p>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach([
                                    ['{SUBSCRIBER_EMAIL}', 'Email del suscriptor'],
                                    ['{SUBSCRIBER_NAME}',  'Nombre (con espacio previo)'],
                                    ['{UNSUBSCRIBE_URL}',  'URL de baja'],
                                    ['{SITE_NAME}',        'Nombre del sitio'],
                                    ['{SITE_URL}',         'URL del sitio'],
                                    ['{CURRENT_YEAR}',     'Año actual'],
                                    ['{SUPPORT_EMAIL}',    'Email de soporte'],
                                ] as [$var, $desc])
                                    <button type="button"
                                            class="btn btn-sm btn-outline-secondary variable-insert font-monospace"
                                            data-variable="{{ $var }}"
                                            title="{{ $desc }}"
                                            style="font-size:.75rem;">{{ $var }}</button>
                                @endforeach
                            </div>
                        </div>

                        <textarea class="d-none" id="fieldContent">{{ old('content', $campaign?->content ?? $defaultContent ?? '') }}</textarea>
                    </div>

                    {{-- Tab Vista previa --}}
                    <div class="tab-pane fade p-3" id="preview-panel" role="tabpanel">
                        @if($campaign)
                            <div id="previewContainer" style="min-height:500px;background:#f8f9fa;border-radius:4px;">
                                <div class="text-center py-5 text-muted" id="previewPlaceholder">
                                    <i class="fas fa-eye fs-1 d-block mb-3"></i>
                                    <p class="mb-0">Guardando los cambios actualizará la vista previa</p>
                                    <small>O haz clic en actualizar para ver el contenido actual</small>
                                </div>
                            </div>
                            <div class="mt-2 d-flex justify-content-between align-items-center">
                                <p class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Las variables se reemplazan con valores de ejemplo en la previsualización
                                </p>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnRefreshPreview">
                                    <i class="fas fa-sync-alt me-1"></i>Actualizar
                                </button>
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-eye fs-1 d-block mb-3"></i>
                                <p class="mb-0">Guarda la campaña primero para ver la vista previa</p>
                            </div>
                        @endif
                    </div>

                </div>

                {{-- Footer --}}
                <div class="card-footer bg-white border-top">
                    <button type="button" class="btn btn-primary w-100 mb-1" id="btnSave">
                        Guardar cambios
                    </button>
                    @if($campaign && $campaign->isDraft())
                        <button type="button" class="btn btn-danger w-100 mb-1" id="btnSendCampaign"
                                data-url="{{ route('manager.newsletter.campaigns.send', $campaign) }}">
                            Enviar a suscriptores
                        </button>
                    @endif
                    <a href="{{ route('manager.newsletter.campaigns.index') }}" class="btn btn-secondary w-100">
                        ← Volver a campañas
                    </a>
                </div>

            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-12 col-lg-4">

            @if($campaign && ($campaign->isDraft() || $campaign->isFailed()))
            {{-- Envío de prueba --}}
            <div class="card mb-3">
                <div class="card-header border-bottom p-3">
                    <h6 class="mb-0 fw-bold">Envío de prueba</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">
                        Recibe un correo de prueba con el diseño actual.
                    </p>
                    <div class="input-group">
                        <input type="email" class="form-control form-control-sm" id="testEmail"
                               placeholder="correo@ejemplo.com"
                               value="{{ auth()->user()?->email }}">
                        <button class="btn btn-sm btn-outline-primary" type="button" id="btnTestSend"
                                data-url="{{ route('manager.newsletter.campaigns.test', $campaign) }}">
                            Enviar
                        </button>
                    </div>
                </div>
            </div>
            @endif

            @if($campaign && $campaign->isSent())
            {{-- Resultados del envío --}}
            <div class="card mb-3">
                <div class="card-header border-bottom p-3">
                    <h6 class="mb-0 fw-bold">Resultados del envío</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-3 py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <p class="text-muted">Destinatarios</p>
                                <strong>{{ number_format($campaign->recipients_count) }}</strong>
                            </div>
                        </div>
                        <div class="list-group-item px-3 py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <p class="text-muted">Enviados</p>
                                <strong class="text-success">{{ number_format($campaign->sent_count) }}</strong>
                            </div>
                        </div>
                        @if($campaign->failed_count > 0)
                        <div class="list-group-item px-3 py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <p class="text-muted">Con error</p>
                                <strong class="text-danger">{{ number_format($campaign->failed_count) }}</strong>
                            </div>
                        </div>
                        @endif
                        <div class="list-group-item px-3 py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <p class="text-muted">Enviada</p>
                                <span class="small">{{ $campaign->sent_at?->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if($campaign)
            {{-- Información --}}
            <div class="card">
                <div class="card-header border-bottom p-3">
                    <h6 class="mb-0 fw-bold">Información</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-3 py-2">
                            <small class="text-muted d-block">Estado</small>
                            @switch($campaign->status)
                                @case('draft')
                                    <span class="badge bg-secondary-subtle text-secondary">Borrador</span>
                                    @break
                                @case('sending')
                                    <span class="badge bg-warning-subtle text-warning">Enviando</span>
                                    @break
                                @case('sent')
                                    <span class="badge bg-success-subtle text-success">Enviada</span>
                                    @break
                                @case('failed')
                                    <span class="badge bg-danger-subtle text-danger">Fallida</span>
                                    @break
                            @endswitch
                        </div>
                        <div class="list-group-item px-3 py-2">
                            <small class="text-muted d-block">Creada</small>
                            <span class="small">{{ $campaign->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @if($campaign->sent_at)
                        <div class="list-group-item px-3 py-2">
                            <small class="text-muted d-block">Enviada</small>
                            <span class="small">{{ $campaign->sent_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @endif
                        @if($campaign->creator)
                        <div class="list-group-item px-3 py-2">
                            <small class="text-muted d-block">Por</small>
                            <span class="small">{{ $campaign->creator->name ?: '—' }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>

{{-- Modal confirmación de envío --}}
@if($campaign && $campaign->isDraft())
<div class="modal fade" id="sendModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar envío</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">¿Enviar la campaña <strong>{{ $campaign->name }}</strong>?</p>
                <p class="text-muted small mb-0">
                    Se enviará a <strong id="sendModalCount"><span class="spinner-border spinner-border-sm align-middle"></span></strong> suscriptores activos.
                    Esta acción no se puede deshacer.
                </p>
            </div>
            <div class="modal-footer d-block">
                <button type="button" class="btn btn-danger w-100 mb-2" id="btnConfirmSend" disabled>
                    Sí, enviar ahora
                </button>
                <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/lib/codemirror.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/theme/monokai.min.css">
<style>
    #codeEditorWrapper .CodeMirror {
        height: 520px;
        font-size: 13px;
        font-family: 'JetBrains Mono', 'Fira Code', 'Consolas', monospace;
        line-height: 1.6;
    }
    .variable-insert { transition: transform .1s ease; }
    .variable-insert:hover { transform: translateY(-1px); }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/lib/codemirror.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/mode/css/css.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/mode/javascript/javascript.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/addon/edit/closetag.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/addon/edit/closebrackets.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/addon/edit/matchbrackets.min.js"></script>

<script>
$(document).ready(function () {

    var isNew     = {{ $campaign ? 'false' : 'true' }};
    var saveUrl   = isNew
        ? '{{ route('manager.newsletter.campaigns.store') }}'
        : '{{ $campaign ? route('manager.newsletter.campaigns.update', $campaign) : '' }}';
    var saveMethod = isNew ? 'POST' : 'PUT';
    var hasChanges = false;

    // ── CodeMirror ────────────────────────────────────────────────────────
    var editor = CodeMirror(document.getElementById('codeEditorWrapper'), {
        value: document.getElementById('fieldContent').value,
        mode: 'htmlmixed',
        theme: 'monokai',
        lineNumbers: true,
        lineWrapping: true,
        autoCloseTags: true,
        autoCloseBrackets: true,
        matchBrackets: true,
        extraKeys: {
            'Ctrl-S': function () { saveCampaign(); },
            'Ctrl-/': 'toggleComment'
        }
    });

    editor.on('change', function () {
        hasChanges = true;
    });

    // ── Inserción de variables ────────────────────────────────────────────
    $(document).on('click', '.variable-insert', function (e) {
        e.preventDefault();
        editor.replaceRange($(this).data('variable'), editor.getCursor());
        editor.focus();
    });

    // ── Guardar campaña ───────────────────────────────────────────────────
    function saveCampaign() {
        var name    = $.trim($('#fieldName').val());
        var subject = $.trim($('#fieldSubject').val());
        var content = editor.getValue();

        if (! name)    { toastr.warning('El nombre es obligatorio.'); return; }
        if (! subject) { toastr.warning('El asunto es obligatorio.'); return; }
        if (! content) { toastr.warning('El contenido es obligatorio.'); return; }

        var $btn = $('#btnSave').prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...'
        );

        $.ajax({
            url: saveUrl,
            method: saveMethod,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
                name:      name,
                subject:   subject,
                preheader: $('#fieldPreheader').val(),
                content:   content,
            },
            success: function (res) {
                hasChanges = false;
                if (isNew) {
                    window.location.href = res.redirect || '{{ route('manager.newsletter.campaigns.index') }}';
                } else {
                    toastr.success(res.message);
                    $btn.prop('disabled', false).text('Guardar cambios');
                }
            },
            error: function (xhr) {
                var errors = xhr.responseJSON?.errors;
                if (errors) {
                    toastr.error(Object.values(errors).flat().join('<br>'), 'Errores de validación');
                } else {
                    toastr.error(xhr.responseJSON?.message || 'Error al guardar.');
                }
                $btn.prop('disabled', false).text('Guardar cambios');
            },
        });
    }

    $('#btnSave').on('click', saveCampaign);

    // ── Vista previa ──────────────────────────────────────────────────────
    @if($campaign)
    function loadPreview() {
        var $container = $('#previewContainer');
        $container.html('<div class="text-center py-5 text-muted"><span class="spinner-border"></span></div>');

        var $iframe = $('<iframe>').css({ width: '100%', border: 'none', background: '#fff' });
        $iframe.attr('src', '{{ route('manager.newsletter.campaigns.preview', $campaign) }}');
        $iframe.on('load', function () {
            try {
                var doc = this.contentDocument || this.contentWindow.document;
                $(this).height(doc.documentElement.scrollHeight);
            } catch (e) {
                $(this).height(600);
            }
        });
        $container.empty().append($iframe);
    }

    $('#preview-tab').on('shown.bs.tab', function () {
        loadPreview();
    });

    $('#btnRefreshPreview').on('click', function () {
        loadPreview();
    });
    @endif

    // ── Envío de prueba ───────────────────────────────────────────────────
    @if($campaign && ($campaign->isDraft() || $campaign->isFailed()))
    $('#btnTestSend').on('click', function () {
        var email = $.trim($('#testEmail').val());
        if (! email) { toastr.warning('Ingresa un correo para la prueba.'); return; }

        var $btn = $(this).prop('disabled', true).text('Enviando...');

        $.ajax({
            url: '{{ route('manager.newsletter.campaigns.test', $campaign) }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { email: email },
            success: function (res) { toastr.success(res.message); },
            error: function (xhr) { toastr.error(xhr.responseJSON?.message || 'Error al enviar prueba.'); },
            complete: function () { $btn.prop('disabled', false).text('Enviar'); },
        });
    });
    @endif

    // ── Modal de envío ────────────────────────────────────────────────────
    @if($campaign && $campaign->isDraft())
    $('#btnSendCampaign').on('click', function () {
        $('#sendModalCount').html('<span class="spinner-border spinner-border-sm align-middle"></span>');
        $('#btnConfirmSend').prop('disabled', true);
        new bootstrap.Modal(document.getElementById('sendModal')).show();

        $.ajax({
            url: '{{ route('manager.newsletter.campaigns.active-count') }}',
            method: 'GET',
            success: function (res) {
                if (res.count === 0) {
                    bootstrap.Modal.getInstance(document.getElementById('sendModal')).hide();
                    toastr.warning('No hay suscriptores activos para enviar la campaña.');
                    return;
                }
                $('#sendModalCount').text(res.count.toLocaleString('es-ES'));
                $('#btnConfirmSend').prop('disabled', false);
            },
            error: function () {
                $('#sendModalCount').text('?');
                $('#btnConfirmSend').prop('disabled', false);
            },
        });
    });

    $('#btnConfirmSend').on('click', function () {
        var $btn = $(this).prop('disabled', true).text('Enviando...');

        $.ajax({
            url: '{{ route('manager.newsletter.campaigns.send', $campaign) }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (res) {
                bootstrap.Modal.getInstance(document.getElementById('sendModal')).hide();
                toastr.success(res.message);
                setTimeout(function () {
                    window.location.href = '{{ route('manager.newsletter.campaigns.index') }}';
                }, 1500);
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message || 'Error al enviar.');
                $btn.prop('disabled', false).text('Sí, enviar ahora');
            },
        });
    });
    @endif

    // ── Advertencia al salir con cambios ──────────────────────────────────
    window.addEventListener('beforeunload', function (e) {
        if (hasChanges) {
            e.preventDefault();
            return '';
        }
    });

});
</script>
@endpush
