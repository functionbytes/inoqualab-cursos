@extends('layouts.managers')

@section('title', $campaign ? 'Editar campaña' : 'Nueva campaña')

@section('page_header')
    @include('managers.includes.card', ['title' => $campaign ? 'Editar campaña' : 'Nueva campaña'])
@endsection

@section('content')

    <div class="row g-3" id="campaign-form-page"
         data-is-new="{{ $campaign ? 'false' : 'true' }}"
         data-save-url="{{ $campaign ? route('manager.newsletter.campaigns.update', $campaign) : route('manager.newsletter.campaigns.store') }}"
         data-save-method="{{ $campaign ? 'PUT' : 'POST' }}"
         data-campaign-exists="{{ $campaign ? 'true' : 'false' }}"
         data-is-draft="{{ $campaign && $campaign->isDraft() ? 'true' : 'false' }}"
         data-is-failed="{{ $campaign && $campaign->isFailed() ? 'true' : 'false' }}"
         data-preview-url="{{ $campaign ? route('manager.newsletter.campaigns.preview', $campaign) : '' }}"
         data-test-url="{{ $campaign ? route('manager.newsletter.campaigns.test', $campaign) : '' }}"
         data-send-url="{{ $campaign ? route('manager.newsletter.campaigns.send', $campaign) : '' }}"
         data-active-count-url="{{ route('manager.newsletter.campaigns.active-count') }}"
         data-index-url="{{ route('manager.newsletter.campaigns.index') }}">

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
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Preencabezado</label>
                        <input type="text" class="form-control" id="fieldPreheader"
                               value="{{ old('preheader', $campaign?->preheader) }}"
                               placeholder="Texto breve visible antes de abrir el email (opcional)"
                               maxlength="255">
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Lista destino</label>
                        <select class="form-select" id="fieldList">
                            <option value="">Todos los suscriptores</option>
                            @foreach ($lists as $list)
                                <option value="{{ $list->id }}" @selected(old('newsletter_list_id', $campaign?->newsletter_list_id) == $list->id)>{{ $list->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Deja "Todos los suscriptores" para enviar a la lista general. Las listas dinámicas se pueblan solas por evento.</small>
                    </div>
                </div>

                {{-- Tabs: Código / Vista previa --}}
                <ul class="nav nav-tabs nav-fill border-bottom" id="editorTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="code-tab" data-bs-toggle="tab"
                                data-bs-target="#code-panel" type="button" role="tab">
                            Código
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="preview-tab" data-bs-toggle="tab"
                                data-bs-target="#preview-panel" type="button" role="tab">
                            Vista previa
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
                                            title="{{ $desc }}">{{ $var }}</button>
                                @endforeach
                            </div>
                        </div>

                        <textarea class="d-none" id="fieldContent">{{ old('content', $campaign?->content ?? $defaultContent ?? '') }}</textarea>
                    </div>

                    {{-- Tab Vista previa --}}
                    <div class="tab-pane fade p-3" id="preview-panel" role="tabpanel">
                        @if($campaign)
                            <div id="previewContainer" class="preview-container">
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
                                    Actualizar
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
                            <span class="small">{{ $campaign->creator->full_name ?: '—' }}</span>
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
<link rel="stylesheet" href="{{ asset('managers/css/views/newsletter/campaigns/form.css') }}">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/lib/codemirror.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/mode/css/css.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/mode/javascript/javascript.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/addon/edit/closetag.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/addon/edit/closebrackets.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/addon/edit/matchbrackets.min.js"></script>
<script src="{{ asset('managers/js/views/newsletter/campaigns/form.js') }}"></script>
@endpush
