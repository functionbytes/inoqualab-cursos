@extends('layouts.managers')

@section('title', 'Crear componente de email')

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

<form method="POST" action="{{ route('mailers.components.store') }}" id="formCreate"
      data-variables-url="{{ route('mailers.components.variables') }}">
    @csrf
    <div class="row g-3">

        {{-- Left Column: Editor --}}
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header border-bottom p-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="mb-0 fw-bold">Editor de código</h5>
                            <p class="text-muted">Crea el contenido del componente</p>
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
                            <label for="name" class="form-label fw-semibold">Nombre del componente</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', '') }}"
                                   placeholder="Ej: Header principal, Footer de emails..." required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="alias" class="form-label fw-semibold">Alias (ID único)</label>
                            <input type="text" class="form-control @error('alias') is-invalid @enderror"
                                   id="alias" name="alias" value="{{ old('alias', '') }}"
                                   placeholder="Ej: email_header, email_footer" required>
                            <small class="form-text text-muted">Identificador único del componente</small>
                            @error('alias')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="code" class="form-label fw-semibold">Código</label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror"
                                   id="code" name="code" value="{{ old('code', '') }}"
                                   placeholder="Ej: header_01" maxlength="100" required>
                            <small class="form-text text-muted">Código de referencia (máx 100 caracteres)</small>
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="type" class="form-label fw-semibold">Tipo</label>
                            <select class="form-select select2 @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="layout" @if(old('type', 'layout') === 'layout') selected @endif>Layout</option>
                                <option value="header" @if(old('type') === 'header') selected @endif>Header</option>
                                <option value="footer" @if(old('type') === 'footer') selected @endif>Footer</option>
                                <option value="component" @if(old('type') === 'component') selected @endif>Componente</option>
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_protected" name="is_protected" value="1" @if(old('is_protected')) checked @endif>
                                <label class="form-check-label" for="is_protected">
                                    <strong>Componente protegido</strong>
                                    <small class="d-block text-muted">Si activas esta opción, el componente no podrá ser eliminado sin desactivar primero la protección</small>
                                </label>
                            </div>
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
                            Vista previa
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="editorTabsContent">
                    <div class="tab-pane fade show active p-0" id="code-panel" role="tabpanel">
                        <textarea class="form-control d-none" id="content" name="content">{{ old('content', '') }}</textarea>
                    </div>
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
                        <div id="previewContainerTab" class="mailer-preview-tab">
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary mb-3" role="status"><span class="visually-hidden">Cargando...</span></div>
                                <p class="text-muted mb-0">Cargando vista previa...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white border-top">
                    <button type="submit" class="btn btn-primary w-100 mb-1">
                        Crear componente
                    </button>
                    <a href="{{ route('mailers.components.index') }}" class="btn btn-secondary w-100">
                        Volver
                    </a>
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="col-12 col-lg-4">
            {{-- Variables panel --}}
            <div class="card mb-3">
                <div class="card-header border-bottom p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 fw-semibold">Variables disponibles</h6>
                            <small class="text-muted d-block">Haz clic para insertar en el editor</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary" id="btnLoadVariables" title="Recargar variables">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div id="variablesPanel" class="mailer-variables-panel">
                        <div class="text-center py-4 text-muted">
                            <div class="spinner-border spinner-border-sm mb-2" role="status"><span class="visually-hidden">Cargando...</span></div>
                            <p class="mb-0 small">Cargando variables...</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Live Preview --}}
            <div class="card">
                <div class="card-header border-bottom p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">Vista previa</h6>
                        <span class="badge bg-primary" id="previewStatus">En vivo</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="previewContainer" class="mailer-preview-main">
                        <div class="text-center py-5">
                            <div class="spinner-border text-success mb-3" role="status"><span class="visually-hidden">Cargando...</span></div>
                            <p class="text-muted mb-0">Cargando vista previa...</p>
                        </div>
                    </div>
                </div>
                <div class="card-footer border-top">
                    <p class="text-muted"><i class="fas fa-info-circle me-1"></i>Actualización automática cada 2 segundos</p>
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
<link rel="stylesheet" href="{{ asset('managers/css/views/mailer/components/create.css') }}">
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
<script src="https://cdn.jsdelivr.net/npm/js-beautify@1.14.9/js/lib/beautify.js"></script>
<script src="https://cdn.jsdelivr.net/npm/js-beautify@1.14.9/js/lib/beautify-html.js"></script>
<script src="{{ asset('managers/js/views/mailer/components/create.js') }}"></script>
@endpush
