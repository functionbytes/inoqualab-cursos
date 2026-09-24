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
        <form method="POST" action="{{ route('mailers.endpoints.update', $endpoint) }}" id="formEdit">
            @csrf
            @method('PATCH')

            <div class="card">

                {{-- Token API --}}
                <div class="card-header border-bottom">
                    <h6 class="mb-1 fw-bold">Token API</h6>
                    <p class="text-muted small mb-0">Clave con la que el sistema externo se identifica. Envíala en el header <code>X-API-Token</code> (o como <code>Bearer</code>) en cada petición; sin ella la API responde 401.</p>
                </div>

                <div class="card-body">
                    <label class="form-label fw-semibold" for="tokenInput">Token actual</label>
                    <div class="input-group">
                        <input type="text" class="form-control font-monospace" id="tokenInput"
                               value="{{ $endpoint->api_token }}" readonly>
                        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#regenerateTokenModal"
                                title="Regenerar token" aria-label="Regenerar token">
                            {!! \App\Html\IconHelper::render('refresh') !!}
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="copyTokenBtn" title="Copiar token" aria-label="Copiar token">
                            {!! \App\Html\IconHelper::render('copy') !!}
                        </button>
                    </div>
                    <small class="text-muted d-block mt-1">Si lo regeneras, el token anterior deja de funcionar de inmediato.</small>
                </div>

                <hr class="my-0">

                {{-- Estadísticas --}}
                <div class="card-body">
                    <div class="repeater-head">
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Estadísticas de uso</h6>
                            <p class="text-muted small mb-0">Peticiones recibidas desde que se creó el endpoint. Un fallo puede ser un envío rechazado por el servidor de correo o un endpoint sin plantilla.</p>
                        </div>
                        <a href="{{ route('mailers.endpoints.logs', $endpoint) }}" class="btn btn-icon btn-sm repeater-add" title="Ver todos los logs" aria-label="Ver todos los logs">
                            {!! \App\Html\IconHelper::render('list') !!}
                        </a>
                    </div>

                    @php $successRate = $stats['total'] > 0 ? round(($stats['success'] / $stats['total']) * 100, 1) : 0; @endphp

                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <div class="card bg-light-secondary h-100 mb-0">
                                <div class="card-body">
                                    <h6 class="card-title mb-2">Total</h6>
                                    <h4 class="mb-1 fw-bold">{{ number_format($stats['total']) }}</h4>
                                    <span class="text-muted">Solicitudes recibidas</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="card bg-light-secondary h-100 mb-0">
                                <div class="card-body">
                                    <h6 class="card-title mb-2">Últimas 24 h</h6>
                                    <h4 class="mb-1 fw-bold">{{ number_format($stats['last_24h']) }}</h4>
                                    <span class="text-muted">Solicitudes recientes</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="card bg-light-secondary h-100 mb-0">
                                <div class="card-body">
                                    <h6 class="card-title mb-2">Exitosos</h6>
                                    <h4 class="mb-1 fw-bold">{{ number_format($stats['success']) }}</h4>
                                    <span class="text-muted">Correos enviados</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="card bg-light-secondary h-100 mb-0">
                                <div class="card-body">
                                    <h6 class="card-title mb-2">Fallidos</h6>
                                    <h4 class="mb-1 fw-bold">{{ number_format($stats['failed']) }}</h4>
                                    <span class="text-muted">Con error</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-0">

                {{-- Tasa de éxito --}}
                @php
                    $rateColor = $successRate >= 90 ? 'success' : ($successRate >= 70 ? 'warning' : 'danger');
                @endphp
                <div class="card-body">
                    <div class="repeater-head">
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Tasa de éxito</h6>
                            <p class="text-muted small mb-0">Porcentaje de peticiones que terminaron en un correo enviado. El color de la barra indica si hay que revisar los logs.</p>
                        </div>
                        <span class="success-rate-value text-{{ $rateColor }}">{{ $stats['total'] > 0 ? $successRate . '%' : '—' }}</span>
                    </div>

                    <div class="progress progress-thin">
                        <div id="successRateBar" class="progress-bar bg-{{ $rateColor }}" data-width="{{ $successRate }}"></div>
                    </div>

                    <ul class="success-rate-legend">
                        <li><span class="success-rate-dot bg-success"></span>90% o más: funciona bien</li>
                        <li><span class="success-rate-dot bg-warning"></span>70–89%: revisa los fallos</li>
                        <li><span class="success-rate-dot bg-danger"></span>Menos de 70%: la integración tiene un problema</li>
                    </ul>

                    <small class="text-muted d-block mt-2">
                        @if($endpoint->last_request_at)
                            Última solicitud: {{ $endpoint->last_request_at->diffForHumans() }}
                        @else
                            Aún no ha recibido solicitudes.
                        @endif
                    </small>
                </div>

                <hr class="my-0">

                {{-- Información básica --}}
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-1">Información básica</h6>
                    <p class="text-muted small mb-3">Cómo se identifica el endpoint en el panel. La fuente y el tipo solo sirven para organizar; no cambian cómo se envía el correo. Si lo desactivas, la API responde 404 a cada petición.</p>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Nombre del endpoint <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $endpoint->name) }}"
                                   placeholder="Ej: PrestaShop Password Reset" required maxlength="255">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Slug</label>
                            <input type="text" class="form-control bg-light" id="slug" value="{{ $endpoint->slug }}" readonly>
                            <small class="form-text text-muted">
                                No se puede cambiar: las integraciones ya llaman a <code>/api/email-endpoints/{{ $endpoint->slug }}/send</code>
                            </small>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Fuente (sistema) <span class="text-danger">*</span></label>
                            <select class="form-select select2 @error('source') is-invalid @enderror" id="source" name="source" required>
                                <option value="">Seleccionar tipo de fuente...</option>
                                <option value="internal" @if(old('source', $endpoint->source) === 'internal') selected @endif>Internal</option>
                                <option value="webhook" @if(old('source', $endpoint->source) === 'webhook') selected @endif>Webhook</option>
                                <option value="api" @if(old('source', $endpoint->source) === 'api') selected @endif>API</option>
                            </select>
                            @error('source')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Tipo de correo <span class="text-danger">*</span></label>
                            <select class="form-select select2 @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="">Seleccionar tipo...</option>
                                <option value="transactional" @if(old('type', $endpoint->type) === 'transactional') selected @endif>Transactional</option>
                                <option value="notification" @if(old('type', $endpoint->type) === 'notification') selected @endif>Notification</option>
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Descripción</label>
                            <textarea class="form-control" id="description" name="description" rows="2"
                                      placeholder="Descripción opcional del endpoint">{{ old('description', $endpoint->description) }}</textarea>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                            <select class="form-select select2 @error('is_active') is-invalid @enderror" id="is_active" name="is_active" required>
                                <option value="1" @if((string) old('is_active', $endpoint->is_active ? '1' : '0') === '1') selected @endif>Activo</option>
                                <option value="0" @if(! ((string) old('is_active', $endpoint->is_active ? '1' : '0') === '1')) selected @endif>Inactivo</option>
                            </select>
                            <small class="form-text text-muted">Los endpoints inactivos rechazarán las peticiones entrantes</small>
                            @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <hr class="my-0">

                {{-- Plantilla --}}
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-1">Plantilla de email</h6>
                    <p class="text-muted small mb-3">Define el asunto y el diseño del correo. Sin plantilla, las peticiones se aceptan pero el envío queda como fallido. Sus variables son las que puedes elegir en el mapeo.</p>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Plantilla de email</label>
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
                    </div>
                </div>

                @include('managers.views.mailer.endpoints._variables')

                <hr class="my-0">

                {{-- Logs recientes --}}
                <div class="card-body">
                    <div class="repeater-head">
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Logs recientes</h6>
                            <p class="text-muted small mb-0">Las últimas 5 peticiones. En el historial completo puedes ver el payload recibido y el motivo de cada fallo.</p>
                        </div>
                        <a href="{{ route('mailers.endpoints.logs', $endpoint) }}" class="btn btn-icon btn-sm repeater-add" title="Ver todos los logs" aria-label="Ver todos los logs">
                            {!! \App\Html\IconHelper::render('list') !!}
                        </a>
                    </div>

                    @if($recentLogs->count() > 0)
                        <div class="table-responsive mb-0">
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
                        <p class="repeater-empty text-muted small mb-0">Este endpoint todavía no ha recibido peticiones.</p>
                    @endif
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary w-100">Guardar cambios</button>
                </div>

            </div>
        </form>
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
                    <li class="mb-2">El destinatario va en el campo <code>email</code> (o <code>recipient_email</code>) del JSON y es obligatorio.</li>
                    <li class="mb-2">La API responde 202 cuando acepta la petición; el correo se envía en segundo plano y el resultado queda en los logs.</li>
                    <li>El JSON puede pesar como máximo 256 KB.</li>
                </ul>
                <p class="small mb-1 fw-semibold">Ejemplo de request:</p>
                <pre class="example-request-pre"><code>POST /api/email-endpoints/{{ $endpoint->slug }}/send
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
                <h5 class="modal-title fw-bold">Regenerar token API</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Al regenerar el token, el anterior deja de funcionar de inmediato y cada integración deberá usar el nuevo.</p>
                <p class="mb-0 fw-semibold">¿Deseas continuar?</p>
            </div>
            <div class="modal-footer d-block">
                <form method="POST" action="{{ route('mailers.endpoints.regenerate-token', $endpoint) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary w-100 mb-2">Regenerar token</button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/mailer/endpoints/edit.css') }}?v={{ @filemtime(public_path('managers/css/views/mailer/endpoints/edit.css')) ?: '1' }}">
<link rel="stylesheet" href="{{ asset('managers/css/views/mailer/endpoints/variables.css') }}?v={{ @filemtime(public_path('managers/css/views/mailer/endpoints/variables.css')) ?: '1' }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/mailer/endpoints/edit.js') }}?v={{ @filemtime(public_path('managers/js/views/mailer/endpoints/edit.js')) ?: '1' }}"></script>
<script src="{{ asset('managers/js/views/mailer/endpoints/variables.js') }}?v={{ @filemtime(public_path('managers/js/views/mailer/endpoints/variables.js')) ?: '1' }}"></script>
@endpush
