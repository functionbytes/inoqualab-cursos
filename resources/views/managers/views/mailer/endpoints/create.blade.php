@extends('layouts.managers')

@section('title', 'Crear email endpoint')

@section('page_header')
    @include('managers.includes.card', ['title' => 'Crear email endpoint'])
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
        <form method="POST" action="{{ route('mailers.endpoints.store') }}" id="formCreate">
            @csrf

            <div class="card">

                {{-- Información básica --}}
                <div class="card-header border-bottom">
                    <h6 class="mb-1 fw-bold">Información básica</h6>
                    <p class="text-muted small mb-0">Define el nombre, slug único y la fuente del endpoint. El slug se usará en la URL de la API.</p>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Nombre del endpoint <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}"
                                   placeholder="Ej: PrestaShop Password Reset" required maxlength="255">
                            <small class="form-text text-muted">Nombre descriptivo para identificar este endpoint</small>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Slug (único) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                   id="slug" name="slug" value="{{ old('slug') }}"
                                   placeholder="prestashop_password_reset" required maxlength="255"
                                   pattern="[a-z0-9\-_]+">
                            <small class="form-text text-muted">
                                URL: <code>/api/email-endpoints/<span id="slugPreview">slug</span>/send</code>
                            </small>
                            @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Fuente (sistema) <span class="text-danger">*</span></label>
                            <select class="form-select select2 @error('source') is-invalid @enderror" id="source" name="source" required>
                                <option value="">Seleccionar tipo de fuente...</option>
                                <option value="internal" @if(old('source') === 'internal') selected @endif>Internal</option>
                                <option value="webhook" @if(old('source') === 'webhook') selected @endif>Webhook</option>
                                <option value="api" @if(old('source') === 'api') selected @endif>API</option>
                            </select>
                            <small class="form-text text-muted">Sistema origen de las peticiones</small>
                            @error('source')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Tipo de correo <span class="text-danger">*</span></label>
                            <select class="form-select select2 @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="">Seleccionar tipo...</option>
                                <option value="transactional" @if(old('type') === 'transactional') selected @endif>Transactional</option>
                                <option value="notification" @if(old('type') === 'notification') selected @endif>Notification</option>
                            </select>
                            <small class="form-text text-muted">Categoría del email (para organización)</small>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Descripción</label>
                            <textarea class="form-control" id="description" name="description" rows="2"
                                      placeholder="Descripción opcional del endpoint">{{ old('description') }}</textarea>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                    <label class="form-check-label" for="is_active">
                                        <strong>Endpoint activo</strong>
                                        <small class="d-block text-muted">Los endpoints inactivos rechazarán las peticiones entrantes</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-0">

                {{-- Plantilla --}}
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-1">Plantilla de email</h6>
                    <p class="text-muted mb-3">Selecciona la plantilla de email que se usará para los envíos.</p>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Plantilla de email</label>
                            <select class="form-select select2 @error('mailer_template_id') is-invalid @enderror"
                                    id="mailer_template_id" name="mailer_template_id">
                                <option value="">-- Seleccionar plantilla --</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}" @if(old('mailer_template_id') == $template->id) selected @endif>
                                        {{ $template->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Plantilla que se usará para enviar los emails</small>
                            @error('mailer_template_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <hr class="my-0">

                {{-- Variables esperadas --}}
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-1">Variables esperadas</h6>
                    <p class="text-muted mb-3">Define las variables que esperas recibir en el JSON desde el sistema externo.</p>

                    <div class="alert alert-light border mb-3">
                        <i class="fas fa-lightbulb me-2 text-warning"></i>
                        <strong>Ejemplo:</strong> Si tu JSON envía <code>{"customer_email": "user@example.com"}</code>, agrega <code>customer_email</code> como variable.
                    </div>
                    <div id="expectedVariablesContainer" class="mb-3"></div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="addExpectedVariable">
                        Agregar variable
                    </button>
                </div>

                <hr class="my-0">

                {{-- Variables obligatorias --}}
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-1">Variables obligatorias</h6>
                    <p class="text-muted mb-3">Marca las variables que son obligatorias en cada request.</p>

                    <div id="requiredVariablesContainer">
                        <p class="text-muted"><i class="fas fa-info-circle me-1"></i>Primero agrega variables esperadas arriba</p>
                    </div>
                </div>

                <hr class="my-0">

                {{-- Mapeo de variables --}}
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-1">Mapeo de variables (opcional)</h6>
                    <p class="text-muted mb-3">Si los nombres en el JSON no coinciden con los de la plantilla, puedes mapearlos aquí.</p>

                    <div class="alert alert-light border mb-3">
                        <i class="fas fa-info-circle me-2 text-info"></i>
                        <strong>Ejemplo:</strong> Si tu JSON envía <code>user.email</code> pero la plantilla usa <code>{email}</code>, mapea: <code>email</code> → <code>user.email</code>
                    </div>
                    <div class="bg-light p-3 rounded">
                        <div id="mappingsContainer">
                            <div class="row g-2 mb-2">
                                <div class="col-5">
                                    <label class="form-label small fw-bold text-uppercase">Variable plantilla</label>
                                </div>
                                <div class="col-5">
                                    <label class="form-label small fw-bold text-uppercase">Ruta JSON</label>
                                </div>
                                <div class="col-2"></div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addMapping">
                            Agregar mapeo
                        </button>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary w-100">Crear endpoint</button>
                </div>

            </div>
        </form>
    </div>

    {{-- Columna derecha: sidebar informativo --}}
    <div class="col-lg-4">

        <div class="card">
            <div class="card-header border-bottom">
                <h6 class="mb-0 fw-bold">Información útil</h6>
            </div>
            <div class="card-body">
                <ul class="text-muted ps-3 mb-3">
                    <li class="mb-2">El <strong>token API</strong> se genera automáticamente al crear el endpoint</li>
                    <li class="mb-2">Podrás ver <strong>estadísticas</strong> y <strong>logs</strong> de uso</li>
                    <li>Los endpoints inactivos <strong>rechazarán</strong> todas las peticiones</li>
                </ul>
                <p class="small mb-1 fw-semibold">Ejemplo de request:</p>
                <pre class="bg-dark text-light p-2 rounded mb-0 example-request-pre"><code>POST /api/email-endpoints/slug/send
Header: X-API-Token: abc123...
{"email": "user@example.com"}</code></pre>
            </div>
        </div>

    </div>

</div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/mailer/endpoints/create.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/mailer/endpoints/create.js') }}"></script>
@endpush
