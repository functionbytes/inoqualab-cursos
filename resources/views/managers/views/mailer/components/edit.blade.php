@extends('layouts.managers')

@section('title', 'Editar componente: ' . ($component->subject ?? $component->alias))

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul><button class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<form method="POST" action="{{ route('mailers.components.update', $component->uid) }}" id="formEdit">
    @csrf
    @method('PATCH')

    <div class="row g-3">

        {{-- Left Column: Editor --}}
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header border-bottom p-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="mb-0 fw-bold">Editor de código</h5>
                            <p class="text-muted">Edita el contenido del componente</p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-info small"><i class="fas fa-keyboard me-1"></i>Ctrl+S para guardar</span>
                            <span class="badge bg-dark text-white" id="editorStatus">Listo</span>
                        </div>
                    </div>
                </div>

                {{-- Toolbar --}}
                <div class="card-body border-bottom p-3">
                    <div class="d-flex gap-3 align-items-center justify-content-between">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-secondary" id="btnFormatCode" title="Formatear código HTML">
                                <i class="fas fa-magic"></i>
                            </button>
                            <button type="button" class="btn btn-secondary" id="btnRefreshPreview" title="Actualizar vista previa">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <p class="text-muted">
                            <i class="fas fa-check-circle text-success me-1"></i>Se guarda automáticamente
                        </p>
                    </div>
                </div>

                {{-- Component Info --}}
                <div class="card-body border-bottom">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="subject" class="form-label fw-semibold">Nombre del componente</label>
                            <input type="text" class="form-control @error('subject') is-invalid @enderror"
                                   id="subject" name="subject"
                                   value="{{ old('subject', $component->subject ?? '') }}"
                                   placeholder="Ej: Header principal, Footer de emails..." required>
                            @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="type" class="form-label fw-semibold">Tipo</label>
                            <select class="form-select select2 @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="partial" @if($component->type === 'partial') selected @endif>Parcial</option>
                                <option value="layout" @if($component->type === 'layout') selected @endif>Layout</option>
                                <option value="component" @if($component->type === 'component') selected @endif>Componente</option>
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_protected" name="is_protected" value="1" @if(old('is_protected', $component->is_protected)) checked @endif>
                                <label class="form-check-label" for="is_protected">
                                    <strong>Componente protegido</strong>
                                    <small class="d-block text-muted">Si activas esta opción, el componente no podrá ser eliminado sin desactivar primero la protección</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 mb-0 mt-3 d-flex align-items-start">
                        <i class="fas fa-info-circle fs-5 me-2 mt-1"></i>
                        <div>
                            <span><strong>Alias:</strong> <code class="text-primary">{{ $component->alias }}</code></span>
                            <span class="ms-3"><strong>Código:</strong> <code class="text-primary">{{ $component->code }}</code></span>
                            @if(in_array($component->alias, ['email_template_header', 'email_template_footer', 'email_template_wrapper']))
                                <span class="badge bg-light text-warning ms-2">Componente del sistema</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Tabs --}}
                <ul class="nav nav-tabs nav-fill border-bottom" id="editorTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="code-tab" data-bs-toggle="tab" data-bs-target="#code-panel" type="button" role="tab">
                            Código
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="preview-tab" data-bs-toggle="tab" data-bs-target="#preview-panel" type="button" role="tab">
                            Vista
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="editorTabsContent">
                    {{-- Code Tab --}}
                    <div class="tab-pane fade show active p-0" id="code-panel" role="tabpanel">
                        {{-- Variables Panel --}}
                        <div class="p-3 bg-light border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="mb-1 fw-semibold">Variables disponibles</h6>
                                    <small class="text-muted d-block">Haz clic para insertar en el editor</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary" id="btnLoadVariables" title="Recargar variables">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                            <div id="variablesPanel" style="max-height:350px;overflow-y:auto;">
                                <div class="text-center py-4 text-muted">
                                    <div class="spinner-border spinner-border-sm mb-2" role="status"><span class="visually-hidden">Cargando...</span></div>
                                    <p class="mb-0 small">Cargando variables...</p>
                                </div>
                            </div>
                        </div>
                        <textarea class="form-control d-none" id="content" name="content">{{ old('content', $component->content ?? '') }}</textarea>
                    </div>

                    {{-- Preview Tab --}}
                    <div class="tab-pane fade p-3" id="preview-panel" role="tabpanel">
                        <div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
                            <div>
                                <h6 class="mb-1 fw-semibold">Vista previa del email</h6>
                                <small class="text-muted d-block">Cambia entre escritorio y móvil</small>
                            </div>
                            <div class="d-flex gap-2 align-items-center">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary active" id="btnDesktopView">
                                        <i class="fas fa-desktop"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" id="btnMobileView">
                                        <i class="fas fa-mobile-alt"></i>
                                    </button>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary" id="btnRefreshPreviewTab">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>
                        <div id="previewContainerTab" style="min-height:500px;max-height:700px;overflow-y:auto;background:#f8f9fa;border-radius:4px;">
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary mb-3" role="status"><span class="visually-hidden">Cargando...</span></div>
                                <p class="text-muted mb-0">Cargando vista previa...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white border-top">
                    <button type="submit" class="btn btn-primary w-100 mb-1">Guardar</button>
                    <a href="{{ route('mailers.components.index') }}" class="btn btn-secondary w-100">Volver</a>
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="col-12 col-lg-4">
            {{-- Keyboard shortcuts --}}
            <div class="card">
                <div class="card-header border-bottom p-3">
                    <h6 class="mb-0 fw-bold">Atajos de teclado</h6>
                    <p class="text-muted">Acelera tu trabajo con estos atajos</p>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Guardar componente</span>
                            <kbd class="bg-dark text-white px-2 py-1 rounded">Ctrl+S</kbd>
                        </div>
                        <div class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Autocompletar</span>
                            <kbd class="bg-dark text-white px-2 py-1 rounded">Ctrl+Space</kbd>
                        </div>
                        <div class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Comentar/Descomentar</span>
                            <kbd class="bg-dark text-white px-2 py-1 rounded">Ctrl+/</kbd>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</form>

@endsection

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/lib/codemirror.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/theme/monokai.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/addon/hint/show-hint.min.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/lib/codemirror.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/mode/css/css.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/mode/javascript/javascript.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/addon/edit/closetag.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/addon/edit/closebrackets.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/addon/hint/show-hint.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/addon/hint/html-hint.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/js-beautify@1.14.9/dist/beautify-html.js"></script>

<script>
let editor;

$(document).ready(function() {
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({ allowClear: false, width: '100%' });
    }

    editor = CodeMirror.fromTextArea(document.getElementById('content'), {
        mode: 'htmlmixed',
        theme: 'monokai',
        lineNumbers: true,
        autoCloseTags: true,
        autoCloseBrackets: true,
        extraKeys: {
            'Ctrl-Space': 'autocomplete',
            'Ctrl-S': function() { $('#formEdit').submit(); },
            'Ctrl-/': 'toggleComment'
        }
    });

    editor.setSize(null, 500);

    let previewTimeout;
    let hasChanges = false;

    function updatePreview() {
        const html = editor.getValue();
        const makeIframe = (container, minHeight) => {
            const $iframe = $('<iframe>').css({ width: '100%', 'min-height': minHeight, border: 'none', display: 'block', background: 'white' });
            $(container).empty().append($iframe);
            $iframe[0].srcdoc = html;
        };
        makeIframe('#previewContainerTab', '500px');
    }

    function loadVariables() {
        $.get('{{ route('mailers.components.variables') }}', function(data) {
            if (!data.success) return;
            let html = '<div class="d-flex flex-wrap gap-1">';
            $.each(data.variables, function(i, group) {
                $.each(group.items, function(j, variable) {
                    html += '<span class="badge bg-light text-dark border variable-insert" style="cursor:pointer;" data-name="' + variable.name + '" title="' + (variable.description || '') + '">' + variable.name + '</span>';
                });
            });
            html += '</div>';
            $('#variablesPanel').html(html);
        }).fail(function() {
            $('#variablesPanel').html('<div class="text-danger small p-2">Error al cargar variables</div>');
        });
    }

    updatePreview();
    loadVariables();

    editor.on('change', function() {
        hasChanges = true;
        clearTimeout(previewTimeout);
        previewTimeout = setTimeout(updatePreview, 2000);
    });

    $(document).on('click', '.variable-insert', function() {
        editor.replaceSelection('{' + $(this).data('name') + '}');
        editor.focus();
    });

    $('#btnRefreshPreview, #btnRefreshPreviewTab').on('click', function(e) { e.preventDefault(); updatePreview(); });
    $('#btnFormatCode').on('click', function(e) {
        e.preventDefault();
        if (typeof html_beautify !== 'undefined') {
            editor.setValue(html_beautify(editor.getValue(), { indent_size: 2 }));
        }
    });
    $('#btnLoadVariables').on('click', function(e) { e.preventDefault(); loadVariables(); });
    $('#btnDesktopView').on('click', function(e) { e.preventDefault(); $('#previewContainerTab').css('width', '100%'); $(this).addClass('active'); $('#btnMobileView').removeClass('active'); });
    $('#btnMobileView').on('click', function(e) { e.preventDefault(); $('#previewContainerTab').css('width', '375px'); $(this).addClass('active'); $('#btnDesktopView').removeClass('active'); });
    $('#preview-tab').on('shown.bs.tab', updatePreview);

    window.addEventListener('beforeunload', function(e) {
        if (hasChanges) { e.preventDefault(); return ''; }
    });

    $('#formEdit').on('submit', function() {
        $('#content').val(editor.getValue());
        hasChanges = false;
        $(this).find('[type="submit"]').prop('disabled', true);
    });
});
</script>
@endpush
