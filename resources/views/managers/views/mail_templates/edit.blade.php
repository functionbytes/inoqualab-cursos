@extends('layouts.managers')

@section('content')

    @include('managers.includes.card', ['title' => 'Editar plantilla: ' . $template->name])

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fs-5 me-2"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-start">
                <i class="fas fa-exclamation-circle fs-5 me-2 mt-1"></i>
                <div>
                    <strong>Errores de validación</strong>
                    <ul class="mb-0 mt-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('manager.mail_templates.update', $template->id) }}" id="formEdit">
        @csrf
        @method('PUT')

        <div class="row g-3">

            {{-- Columna izquierda: Editor --}}
            <div class="col-12 col-lg-8">
                <div class="card">

                    {{-- Encabezado --}}
                    <div class="card-header border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h5 class="mb-0 fw-bold">Editor de código</h5>
                                <small class="text-muted">Edita el contenido HTML de la plantilla</small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge text-info small">
                                    <i class="fas fa-keyboard me-1"></i>Ctrl+S para guardar
                                </span>
                                <span class="badge bg-black text-white" id="editorStatus">Listo</span>
                            </div>
                        </div>
                    </div>

                    {{-- Barra de herramientas --}}
                    <div class="card-body border-bottom p-3">
                        <div class="d-flex gap-3 align-items-center justify-content-between flex-wrap">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-secondary btn-sm" id="btnFormatCode"
                                        title="Formatear código HTML (beautify)">
                                    <i class="fas fa-wand-magic-sparkles me-1"></i>Formatear HTML
                                </button>
                            </div>
                            <small class="text-muted d-none d-md-inline">
                                <i class="fas fa-circle-check text-success me-1"></i>Vista previa se actualiza automáticamente
                            </small>
                        </div>
                    </div>

                    {{-- Campos de la plantilla --}}
                    <div class="card-body border-bottom">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Nombre interno</label>
                                <input type="text" name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $template->name) }}"
                                       placeholder="Nombre descriptivo de la plantilla">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Asunto del correo</label>
                                <input type="text" name="subject"
                                       class="form-control @error('subject') is-invalid @enderror"
                                       value="{{ old('subject', $template->subject) }}"
                                       placeholder="Ej: Confirmación de pedido #{ORDER_NUMBER}" required>
                                <div class="form-text">Puedes usar variables: <code>{CUSTOMER_NAME}</code>, <code>{ORDER_NUMBER}</code></div>
                                @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="alert alert-info border-0 mb-0 mt-3 d-flex align-items-start">
                            <i class="fas fa-info-circle fs-5 me-2 mt-1"></i>
                            <div>
                                <strong>Clave:</strong> <code class="text-primary">{{ $template->key }}</code>
                                @if($template->description)
                                    <span class="text-muted ms-2">— {{ $template->description }}</span>
                                @endif
                            </div>
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

                        {{-- Tab 1: Editor de código --}}
                        <div class="tab-pane fade show active p-0" id="code-panel" role="tabpanel">

                            <div id="codeEditorWrapper"></div>

                            {{-- Panel de variables --}}
                            <div class="border-top p-3 bg-light">
                                <div class="d-flex justify-content-between align-items-center gap-3 mb-2">
                                    <div>
                                        <h6 class="mb-0 fw-semibold text-dark">Variables disponibles</h6>
                                        <small class="text-muted">Haz clic en una variable para insertarla en el cursor</small>
                                    </div>
                                </div>

                                @php
                                    $globalVars = [
                                        'SITE_NAME'     => 'Nombre del sitio',
                                        'SITE_URL'      => 'URL del sitio',
                                        'LOGO_URL'      => 'URL del logo',
                                        'SUPPORT_EMAIL' => 'Correo de soporte',
                                        'SUPPORT_PHONE' => 'Teléfono de soporte',
                                        'CURRENT_YEAR'  => 'Año actual',
                                    ];
                                @endphp

                                <div class="mb-2">
                                    <small class="fw-semibold text-uppercase text-muted d-block mb-1" style="font-size:10px;letter-spacing:.5px;">Globales</small>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($globalVars as $var => $desc)
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary variable-insert"
                                                    data-variable-name="{{ $var }}"
                                                    title="{{ $desc }}">
                                                <code class="small">{!! '{' . $var . '}' !!}</code>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                @if($template->variables)
                                    <div>
                                        <small class="fw-semibold text-uppercase text-muted d-block mb-1" style="font-size:10px;letter-spacing:.5px;">Esta plantilla</small>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($template->variables as $var => $desc)
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-primary variable-insert"
                                                        data-variable-name="{{ $var }}"
                                                        title="{{ $desc }}">
                                                    <code class="small">{!! '{' . $var . '}' !!}</code>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <textarea class="d-none" id="content" name="content">{{ old('content', $template->content) }}</textarea>
                        </div>

                        {{-- Tab 2: Vista previa --}}
                        <div class="tab-pane fade p-3" id="preview-panel" role="tabpanel">
                            <div class="d-flex align-items-center justify-content-between gap-3 mb-3 flex-wrap">
                                <div>
                                    <h6 class="mb-0 fw-semibold text-dark">Vista previa del email</h6>
                                    <small class="text-muted">Cambia entre escritorio y móvil para previsualizar</small>
                                </div>
                                <div class="d-flex gap-2 align-items-center">
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Device preview">
                                        <button type="button" class="btn btn-outline-primary active"
                                                id="btnDesktopView" data-width="100%"
                                                title="Vista Desktop (100%)">
                                            <i class="fas fa-desktop"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-primary"
                                                id="btnMobileView" data-width="375px"
                                                title="Vista Móvil (375px)">
                                            <i class="fas fa-mobile-screen"></i>
                                        </button>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary" id="btnRefreshPreview"
                                            title="Actualizar vista previa">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="previewContainer"
                                 style="min-height:500px;max-height:700px;overflow-y:auto;background:#f8f9fa;border-radius:4px;transition:max-width .3s ease;max-width:100%;margin:0 auto;">
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-eye fs-1 d-block mb-3"></i>
                                    <p class="mb-0">Cambia al tab de código para comenzar a editar</p>
                                    <small>La vista previa aparecerá aquí</small>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Las variables globales se reemplazan con los valores reales del sitio. Las variables de plantilla quedan como <code>{VAR}</code>.
                                </small>
                            </div>
                        </div>
                    </div>

                    {{-- Botones de acción --}}
                    <div class="card-footer bg-white border-top">
                        <button type="submit" class="btn btn-primary w-100 mb-1">
                            <i class="fas fa-save me-1"></i>Guardar cambios
                        </button>
                        <button type="button" class="btn btn-outline-info w-100 mb-1"
                                data-bs-toggle="modal" data-bs-target="#modalTestEmail">
                            <i class="fas fa-paper-plane me-1"></i>Enviar correo de prueba
                        </button>
                        <a href="{{ route('manager.mail_templates') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-arrow-left me-1"></i>Volver al listado
                        </a>
                    </div>
                </div>
            </div>

            {{-- Columna derecha: Info + Atajos --}}
            <div class="col-12 col-lg-4">

                {{-- Info de la plantilla --}}
                <div class="card mb-3">
                    <div class="card-header bg-info-subtle border-bottom p-3">
                        <h6 class="mb-0 fw-bold">Información de la plantilla</h6>
                        <small class="text-muted">Datos de referencia (no editables)</small>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item px-3 py-2">
                                <small class="text-muted d-block">Clave del sistema</small>
                                <code class="text-primary">{{ $template->key }}</code>
                            </div>
                            @if($template->description)
                                <div class="list-group-item px-3 py-2">
                                    <small class="text-muted d-block">Descripción</small>
                                    <span class="small">{{ $template->description }}</span>
                                </div>
                            @endif
                            <div class="list-group-item px-3 py-2">
                                <small class="text-muted d-block">Última actualización</small>
                                <span class="small">{{ $template->updated_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Atajos de teclado --}}
                <div class="card">
                    <div class="card-header border-bottom p-3">
                        <h6 class="mb-0 fw-bold">Atajos de teclado</h6>
                        <small class="text-muted">Acelera tu trabajo con estos atajos</small>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item px-3 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Guardar plantilla</span>
                                    <kbd class="bg-dark text-white px-2 py-1 rounded small">Ctrl+S</kbd>
                                </div>
                            </div>
                            <div class="list-group-item px-3 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Autocompletar HTML</span>
                                    <kbd class="bg-dark text-white px-2 py-1 rounded small">Ctrl+Space</kbd>
                                </div>
                            </div>
                            <div class="list-group-item px-3 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Comentar/Descomentar</span>
                                    <kbd class="bg-dark text-white px-2 py-1 rounded small">Ctrl+/</kbd>
                                </div>
                            </div>
                            <div class="list-group-item px-3 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Deshacer</span>
                                    <kbd class="bg-dark text-white px-2 py-1 rounded small">Ctrl+Z</kbd>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light p-2 text-center">
                        <small class="text-muted">
                            <i class="fas fa-lightbulb me-1"></i>
                            El editor detecta y resalta sintaxis HTML, CSS y JavaScript
                        </small>
                    </div>
                </div>
            </div>

        </div>
    </form>

{{-- Modal: Enviar correo de prueba --}}
<div class="modal fade" id="modalTestEmail" tabindex="-1" aria-labelledby="modalTestEmailLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTestEmailLabel">
                    <i class="fas fa-paper-plane me-2"></i>Enviar correo de prueba
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">
                    Se enviará el contenido actual del editor (incluyendo cambios no guardados) a la dirección indicada.
                    Las variables globales se reemplazarán con los valores reales del sitio.
                </p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Dirección de correo</label>
                    <input type="email" class="form-control" id="testEmailInput"
                           placeholder="ejemplo@correo.com" required>
                    <div class="form-text">Puedes usar tu propio correo para revisar el resultado.</div>
                </div>
                <div id="testEmailResult" class="d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info" id="btnSendTestEmail">
                    <i class="fas fa-paper-plane me-1"></i>Enviar prueba
                </button>
            </div>
        </div>
    </div>
</div>

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/lib/codemirror.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/theme/monokai.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/addon/hint/show-hint.min.css">
<style>
    #codeEditorWrapper .CodeMirror {
        height: 520px;
        font-size: 13px;
        font-family: 'JetBrains Mono', 'Fira Code', 'Consolas', monospace;
        line-height: 1.6;
    }
    .variable-insert {
        transition: transform .1s ease;
    }
    .variable-insert:hover {
        transform: translateY(-1px);
    }
    #previewContainer iframe {
        display: block;
        width: 100%;
        border: none;
        background: #fff;
        overflow: hidden;
    }
    #previewContainer {
        transition: max-width .3s ease;
    }
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
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/addon/hint/show-hint.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/addon/hint/html-hint.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/addon/hint/css-hint.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/js-beautify@1.14.9/dist/beautify-html.js"></script>

<script>
$(document).ready(function () {

    var previewTimeout;
    var hasChanges = false;

    // ── CodeMirror ────────────────────────────────────────────────────────
    var editor = CodeMirror(document.getElementById('codeEditorWrapper'), {
        value: document.getElementById('content').value,
        mode: 'htmlmixed',
        theme: 'monokai',
        lineNumbers: true,
        lineWrapping: true,
        autoCloseTags: true,
        autoCloseBrackets: true,
        matchBrackets: true,
        extraKeys: {
            'Ctrl-Space': 'autocomplete',
            'Ctrl-S': function () { submitForm(); },
            'Ctrl-/': 'toggleComment'
        }
    });

    // ── Estado del editor ─────────────────────────────────────────────────
    function setStatus(text, variant) {
        var $el = $('#editorStatus');
        $el.text(text).removeClass('bg-black bg-warning bg-success bg-danger text-dark text-white');
        if (variant === 'warning') {
            $el.addClass('bg-warning text-dark');
        } else if (variant === 'success') {
            $el.addClass('bg-success text-white');
        } else if (variant === 'danger') {
            $el.addClass('bg-danger text-white');
        } else {
            $el.addClass('bg-black text-white');
        }
    }

    // ── Vista previa AJAX ─────────────────────────────────────────────────
    function updatePreview() {
        var content = editor.getValue();
        $.ajax({
            url: '{{ route('manager.mail_templates.preview_ajax', $template->id) }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                content: content
            },
            dataType: 'json',
            beforeSend: function () {
                setStatus('Cargando...', 'default');
            },
            success: function (data) {
                if (!data.success) return;
                var $container = $('#previewContainer');
                var $iframe = $('<iframe>').css({ width: '100%', border: 'none', background: '#fff' });
                $container.empty().append($iframe);
                $iframe[0].srcdoc = data.html;
                $iframe.on('load', function () {
                    try {
                        var doc = this.contentDocument || this.contentWindow.document;
                        $(this).height(doc.documentElement.scrollHeight);
                    } catch (e) {
                        $(this).height(600);
                    }
                });
                setStatus('En vivo', 'success');
            },
            error: function () {
                $('#previewContainer').html(
                    '<div class="alert alert-danger m-3"><i class="fas fa-exclamation-circle me-2"></i>Error al cargar vista previa</div>'
                );
                setStatus('Error', 'danger');
            }
        });
    }

    // ── Evento de cambio en el editor ─────────────────────────────────────
    editor.on('change', function () {
        hasChanges = true;
        setStatus('Modificado', 'warning');
        clearTimeout(previewTimeout);
        previewTimeout = setTimeout(updatePreview, 2000);
    });

    // ── Botón: Formatear HTML ─────────────────────────────────────────────
    $('#btnFormatCode').on('click', function (e) {
        e.preventDefault();
        var formatted = html_beautify(editor.getValue(), {
            indent_size: 2,
            wrap_line_length: 120,
            preserve_newlines: true,
            max_preserve_newlines: 2,
            unformatted: ['a', 'span', 'strong', 'em', 'b', 'i', 'code']
        });
        editor.setValue(formatted);
        editor.focus();
        setStatus('Formateado', 'success');
        setTimeout(function () { setStatus('Listo', 'default'); }, 1500);
    });

    // ── Botón: Actualizar vista previa ────────────────────────────────────
    $('#btnRefreshPreview').on('click', function (e) {
        e.preventDefault();
        updatePreview();
        $(this).prop('disabled', true);
        setTimeout(function () { $('#btnRefreshPreview').prop('disabled', false); }, 1000);
    });

    // ── Inserción de variables ────────────────────────────────────────────
    $(document).on('click', '.variable-insert', function (e) {
        e.preventDefault();
        var varName = $(this).data('variable-name');
        editor.replaceRange('{' + varName + '}', editor.getCursor());
        editor.focus();
    });

    // ── Toggle Desktop / Móvil ────────────────────────────────────────────
    $('#btnDesktopView, #btnMobileView').on('click', function () {
        var width = $(this).data('width');
        $('#previewContainer').css('max-width', width);
        $('#btnDesktopView, #btnMobileView').removeClass('active');
        $(this).addClass('active');
    });

    // ── Abrir tab de vista previa → actualizar ────────────────────────────
    $('#preview-tab').on('shown.bs.tab', function () {
        updatePreview();
    });

    // ── Envío del formulario ──────────────────────────────────────────────
    function submitForm() {
        document.getElementById('content').value = editor.getValue();
        hasChanges = false;
        document.getElementById('formEdit').submit();
    }

    $('#formEdit').on('submit', function () {
        document.getElementById('content').value = editor.getValue();
        hasChanges = false;
        $(this).find('[type="submit"]').prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...'
        );
        return true;
    });

    // ── Advertencia al salir con cambios ──────────────────────────────────
    window.addEventListener('beforeunload', function (e) {
        if (hasChanges) {
            e.preventDefault();
            return '';
        }
    });

    // ── Enviar correo de prueba ───────────────────────────────────────────
    $('#btnSendTestEmail').on('click', function () {
        var email = $('#testEmailInput').val().trim();
        if (!email) {
            $('#testEmailInput').addClass('is-invalid').focus();
            return;
        }
        $('#testEmailInput').removeClass('is-invalid');

        var $btn = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Enviando...');
        $('#testEmailResult').addClass('d-none');

        $.ajax({
            url: '{{ route('manager.mail_templates.send_test', $template->id) }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                test_email: email,
                content: editor.getValue(),
                subject: $('input[name="subject"]').val()
            },
            dataType: 'json',
            success: function (data) {
                $('#testEmailResult')
                    .removeClass('d-none alert-danger')
                    .addClass('alert alert-success')
                    .html('<i class="fas fa-check-circle me-2"></i>' + data.message);
            },
            error: function (xhr) {
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error al enviar el correo';
                $('#testEmailResult')
                    .removeClass('d-none alert-success')
                    .addClass('alert alert-danger')
                    .html('<i class="fas fa-exclamation-circle me-2"></i>' + msg);
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i>Enviar prueba');
            }
        });
    });

    $('#modalTestEmail').on('hidden.bs.modal', function () {
        $('#testEmailInput').val('').removeClass('is-invalid');
        $('#testEmailResult').addClass('d-none').removeClass('alert alert-success alert-danger').html('');
    });

});
</script>
@endpush

@endsection
