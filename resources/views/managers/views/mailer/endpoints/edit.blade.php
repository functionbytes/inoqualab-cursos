@extends('layouts.managers')

@section('title', 'Editar endpoint: ' . $endpoint->name)

@section('page_header')
    @include('managers.includes.card', ['title' => 'Editar endpoint: ' . $endpoint->name])
@endsection

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

<div class="row g-4 align-items-start">

{{-- Columna izquierda: formulario --}}
<div class="col-lg-8">
<div class="card">
    <form method="POST" action="{{ route('mailers.endpoints.update', $endpoint) }}" id="formEdit">
        @csrf
        @method('PATCH')

        <div class="card-header border-bottom p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-bold">Editar endpoint de email</h5>
                    <p class="text-muted mb-0 mt-1">Modifica la configuración del endpoint <strong>{{ $endpoint->name }}</strong>.</p>
                </div>
                <a href="{{ route('mailers.endpoints.index') }}" class="btn btn-light">Atrás</a>
            </div>
        </div>

        <div class="card-body">
            <div class="row g-3">

                {{-- Token API --}}
                <div class="col-12">
                    <h6 class="fw-bold mb-1 border-bottom pb-2">Token API</h6>
                    <p class="text-muted small mb-3">Envía este token en el header <code>X-API-Token</code> para autenticar las peticiones.</p>
                </div>

                <div class="col-12 col-lg-8">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-lock text-primary"></i></span>
                        <input type="text" class="form-control font-monospace bg-light" id="tokenInput"
                               value="{{ $endpoint->api_token }}" readonly>
                        <button class="btn btn-outline-secondary" type="button" id="copyTokenBtn" title="Copiar token">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <button type="button" class="btn btn-outline-warning w-100" data-bs-toggle="modal" data-bs-target="#regenerateTokenModal">
                        Regenerar token
                    </button>
                    <small class="form-text text-muted d-block text-center mt-1">
                        <i class="fas fa-exclamation-triangle me-1 text-warning"></i>El token anterior dejará de funcionar
                    </small>
                </div>

                {{-- Estadísticas --}}
                <div class="col-12">
                    <hr>
                    <h6 class="fw-bold mb-1 border-bottom pb-2">Estadísticas de uso</h6>
                </div>

                @php $successRate = $stats['total'] > 0 ? round(($stats['success'] / $stats['total']) * 100, 1) : 0; @endphp

                <div class="col-6 col-md-3">
                    <div class="card border-0 stat-card-soft-primary">
                        <div class="card-body p-3 text-center">
                            <h3 class="mb-1 fw-bold text-primary">{{ number_format($stats['total']) }}</h3>
                            <p class="text-muted">Total requests</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 stat-card-soft-info">
                        <div class="card-body p-3 text-center">
                            <h3 class="mb-1 fw-bold text-info">{{ number_format($stats['last_24h']) }}</h3>
                            <p class="text-muted">Últimas 24h</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 stat-card-soft-success">
                        <div class="card-body p-3 text-center">
                            <h3 class="mb-1 fw-bold text-success">{{ number_format($stats['success']) }}</h3>
                            <p class="text-muted">Exitosos</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 stat-card-soft-danger">
                        <div class="card-body p-3 text-center">
                            <h3 class="mb-1 fw-bold text-danger">{{ number_format($stats['failed']) }}</h3>
                            <p class="text-muted">Fallidos</p>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="fw-semibold">Tasa de éxito</small>
                        <small class="fw-semibold {{ $successRate >= 90 ? 'text-success' : ($successRate >= 70 ? 'text-warning' : 'text-danger') }}">{{ $successRate }}%</small>
                    </div>
                    <div class="progress progress-thin">
                        <div id="successRateBar"
                             class="progress-bar {{ $successRate >= 90 ? 'bg-success' : ($successRate >= 70 ? 'bg-warning' : 'bg-danger') }}"
                             data-width="{{ $successRate }}"></div>
                    </div>
                    @if($endpoint->last_request_at)
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-clock me-1"></i>Última solicitud: {{ $endpoint->last_request_at->diffForHumans() }}
                        </small>
                    @endif
                </div>

                <div class="col-12 col-md-6">
                    <a href="{{ route('mailers.endpoints.logs', $endpoint) }}" class="btn btn-outline-primary w-100">
                        Ver todos los logs
                    </a>
                </div>

                {{-- Información básica --}}
                <div class="col-12">
                    <hr>
                    <h6 class="fw-bold mb-1 border-bottom pb-2">Información básica</h6>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">Nombre del endpoint <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name', $endpoint->name) }}"
                           placeholder="Ej: PrestaShop Password Reset" required maxlength="255">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">Slug (único) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('slug') is-invalid @enderror"
                           id="slug" name="slug" value="{{ old('slug', $endpoint->slug) }}"
                           required maxlength="255" pattern="[a-z0-9\-_]+">
                    <small class="form-text text-muted">
                        URL: <code>/api/email-endpoints/<strong>{{ $endpoint->slug }}</strong>/send</code>
                    </small>
                    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">Fuente (sistema) <span class="text-danger">*</span></label>
                    <select class="form-select select2 @error('source') is-invalid @enderror" id="source" name="source" required>
                        <option value="">Seleccionar tipo de fuente...</option>
                        <option value="internal" @if(old('source', $endpoint->source) === 'internal') selected @endif>Internal</option>
                        <option value="webhook" @if(old('source', $endpoint->source) === 'webhook') selected @endif>Webhook</option>
                        <option value="api" @if(old('source', $endpoint->source) === 'api') selected @endif>API</option>
                    </select>
                    @error('source')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">Tipo de correo <span class="text-danger">*</span></label>
                    <select class="form-select select2 @error('type') is-invalid @enderror" id="type" name="type" required>
                        <option value="">Seleccionar tipo...</option>
                        <option value="transactional" @if(old('type', $endpoint->type) === 'transactional') selected @endif>Transactional</option>
                        <option value="notification" @if(old('type', $endpoint->type) === 'notification') selected @endif>Notification</option>
                    </select>
                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Descripción</label>
                    <textarea class="form-control" id="description" name="description" rows="2"
                              placeholder="Descripción opcional del endpoint">{{ old('description', $endpoint->description) }}</textarea>
                </div>

                <div class="col-12 col-md-6">
                    <div class="border rounded p-3">
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" @if($endpoint->is_active) checked @endif>
                            <label class="form-check-label" for="is_active">
                                <strong>Endpoint activo</strong>
                                <small class="d-block text-muted">Los endpoints inactivos rechazarán las peticiones entrantes</small>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Plantilla --}}
                <div class="col-12">
                    <hr>
                    <h6 class="fw-bold mb-1 border-bottom pb-2">Plantilla de email</h6>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">Plantilla de email</label>
                    <select class="form-select select2 @error('mailer_template_id') is-invalid @enderror"
                            id="mailer_template_id" name="mailer_template_id">
                        <option value="">-- Seleccionar plantilla --</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}" @if(old('mailer_template_id', $endpoint->mailer_template_id) == $template->id) selected @endif>
                                {{ $template->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('mailer_template_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Variables esperadas --}}
                <div class="col-12">
                    <hr>
                    <h6 class="fw-bold mb-1 border-bottom pb-2">Variables esperadas</h6>
                </div>

                <div class="col-12">
                    <div class="alert alert-light border mb-3">
                        <i class="fas fa-lightbulb me-2 text-warning"></i>
                        <strong>Ejemplo:</strong> Si tu JSON envía <code>{"customer_email": "user@example.com"}</code>, agrega <code>customer_email</code> como variable.
                    </div>
                    <div id="expectedVariablesContainer" class="mb-3">
                        @foreach($endpoint->expected_variables ?? [] as $var)
                            <div class="input-group mb-2">
                                <span class="input-group-text bg-light"><i class="fas fa-cube text-primary"></i></span>
                                <input type="text" class="form-control expected-var-input" name="expected_variables[]"
                                       placeholder="Ej: customer_email" value="{{ $var }}">
                                <button type="button" class="btn btn-outline-danger remove-variable"><i class="fas fa-times"></i></button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="addExpectedVariable">
                        Agregar variable
                    </button>
                </div>

                {{-- Variables obligatorias --}}
                <div class="col-12">
                    <hr>
                    <h6 class="fw-bold mb-1 border-bottom pb-2">Variables obligatorias</h6>
                </div>

                <div class="col-12">
                    <div id="requiredVariablesContainer">
                        @if(!empty($endpoint->expected_variables))
                            @foreach($endpoint->expected_variables as $index => $var)
                                <div class="form-check form-check-inline mb-2">
                                    <input class="form-check-input" type="checkbox" name="required_variables[]"
                                           id="required_{{ $index }}" value="{{ $var }}"
                                           @if(in_array($var, $endpoint->required_variables ?? [])) checked @endif>
                                    <label class="form-check-label" for="required_{{ $index }}">
                                        <span class="badge bg-light text-dark border rounded-pill py-1 px-2">{{ $var }}</span>
                                    </label>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted"><i class="fas fa-info-circle me-1"></i>Primero agrega variables esperadas arriba</p>
                        @endif
                    </div>
                </div>

                {{-- Mapeo de variables --}}
                <div class="col-12">
                    <hr>
                    <h6 class="fw-bold mb-1 border-bottom pb-2">Mapeo de variables (opcional)</h6>
                </div>

                <div class="col-12">
                    <div class="bg-light p-3 rounded">
                        <div id="mappingsContainer">
                            <div class="row g-2 mb-2">
                                <div class="col-5"><label class="form-label small fw-bold text-uppercase">Variable plantilla</label></div>
                                <div class="col-5"><label class="form-label small fw-bold text-uppercase">Ruta JSON</label></div>
                                <div class="col-2"></div>
                            </div>
                            @php $mappings = $endpoint->variable_mappings ?? []; @endphp
                            @foreach($mappings as $templateVar => $jsonPath)
                                <div class="row g-2 mb-2 mapping-row">
                                    <div class="col-5">
                                        <input type="text" class="form-control form-control-sm mapping-template" placeholder="email" value="{{ $templateVar }}">
                                    </div>
                                    <div class="col-5">
                                        <input type="text" class="form-control form-control-sm mapping-json" placeholder="user.email" value="{{ $jsonPath }}">
                                    </div>
                                    <div class="col-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-mapping w-100"><i class="fas fa-times"></i></button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addMapping">
                            Agregar mapeo
                        </button>
                    </div>
                </div>

                {{-- Logs recientes --}}
                <div class="col-12">
                    <hr>
                    <h6 class="fw-bold mb-1 border-bottom pb-2">Logs recientes</h6>
                </div>

                <div class="col-12">
                    @if($recentLogs->count() > 0)
                        <div class="table-responsive mb-3">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Estado</th>
                                        <th>Email</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentLogs as $log)
                                        @php
                                            $logBadgeClass = match ($log->status) {
                                                \App\Enums\EndpointLogStatus::Success => 'bg-success',
                                                \App\Enums\EndpointLogStatus::Failed => 'bg-danger',
                                                \App\Enums\EndpointLogStatus::Pending => 'bg-warning',
                                            };
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="badge rounded-pill {{ $logBadgeClass }} text-white">
                                                    {{ ucfirst($log->status->value) }}
                                                </span>
                                            </td>
                                            <td class="text-muted">{{ Str::limit($log->recipient_email, 30) }}</td>
                                            <td class="text-muted">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-light border text-center">
                            <i class="fas fa-inbox fs-4 text-muted mb-2 d-block"></i>
                            <p class="text-muted mb-0">No hay logs registrados aún</p>
                        </div>
                    @endif
                </div>

            </div>
        </div>

        <div class="card-footer border-top">
            <button type="submit" class="btn btn-primary w-100 mb-1">Guardar cambios</button>
            <a href="{{ route('mailers.endpoints.index') }}" class="btn btn-light w-100">Cancelar</a>
        </div>

    </form>
</div>
</div>

{{-- Columna derecha: sidebar informativo --}}
<div class="col-lg-4">

    <div class="card mb-3">
        <div class="card-header border-bottom">
            <h6 class="mb-0 fw-bold">Información del endpoint</h6>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                <div class="list-group-item px-3 py-2">
                    <small class="text-muted d-block">Total solicitudes</small>
                    <span class="small fw-semibold">{{ number_format($endpoint->requests_count) }}</span>
                </div>
                <div class="list-group-item px-3 py-2">
                    <small class="text-muted d-block">Creado</small>
                    <span class="small">{{ $endpoint->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="list-group-item px-3 py-2">
                    <small class="text-muted d-block">Última actualización</small>
                    <span class="small">{{ $endpoint->updated_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-bottom">
            <h6 class="mb-0 fw-bold">Información útil</h6>
        </div>
        <div class="card-body">
            <ul class="text-muted ps-3 mb-3">
                <li class="mb-2">El <strong>token API</strong> se envía en el header <code>X-API-Token</code></li>
                <li class="mb-2">Regenerar el token invalida el anterior de inmediato</li>
                <li>Los endpoints inactivos <strong>rechazarán</strong> todas las peticiones</li>
            </ul>
            <p class="small mb-1 fw-semibold">Ejemplo de request:</p>
            <pre class="bg-dark text-light p-2 rounded mb-0 example-request-pre"><code>POST /api/email-endpoints/{{ $endpoint->slug }}/send
Header: X-API-Token: {{ Str::limit($endpoint->api_token, 12, '...') }}
{"email": "user@example.com"}</code></pre>
        </div>
    </div>

</div>

</div>

{{-- Regenerate Token Modal --}}
<div class="modal fade" id="regenerateTokenModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>Regenerar token API
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning border-0">
                    <strong><i class="fas fa-exclamation-circle me-1"></i>Advertencia:</strong>
                    Al regenerar el token, el token anterior dejará de funcionar inmediatamente.
                </div>
                <p class="mb-0 fw-semibold">¿Deseas continuar?</p>
            </div>
            <div class="modal-footer">
                <form method="POST" action="{{ route('mailers.endpoints.regenerate-token', $endpoint) }}">
                    @csrf
                    <button type="submit" class="btn btn-warning w-100 mb-2">
                        Regenerar
                    </button>
                    <button type="button" class="btn btn-light w-100" data-bs-dismiss="modal">Cancelar</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/mailer/endpoints/edit.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/mailer/endpoints/edit.js') }}"></script>
@endpush
