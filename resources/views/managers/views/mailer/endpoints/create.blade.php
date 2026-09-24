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
                    <p class="text-muted small mb-0">Cómo se identifica el endpoint. El slug forma la URL de la API y no se puede cambiar después de crearlo; la fuente y el tipo solo sirven para organizar.</p>
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
                                   placeholder="prestashop-password-reset" required maxlength="100"
                                   pattern="[a-z0-9\-]+" title="Solo minúsculas, números y guiones">
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
                            <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                            <select class="form-select select2 @error('is_active') is-invalid @enderror" id="is_active" name="is_active" required>
                                <option value="1" @if((string) old('is_active', '1') === '1') selected @endif>Activo</option>
                                <option value="0" @if(! ((string) old('is_active', '1') === '1')) selected @endif>Inactivo</option>
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

                @include('managers.views.mailer.endpoints._variables')

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
                    <li class="mb-2">El token API se genera al crear el endpoint; lo verás en la pantalla de edición.</li>
                    <li class="mb-2">El destinatario va en el campo <code>email</code> (o <code>recipient_email</code>) del JSON y es obligatorio.</li>
                    <li>Si el endpoint está inactivo, la API responde 404 a cada petición.</li>
                </ul>
                <p class="small mb-1 fw-semibold">Ejemplo de request:</p>
                <pre class="example-request-pre"><code>POST /api/email-endpoints/slug/send
Header: X-API-Token: abc123...
{"email": "user@example.com"}</code></pre>
            </div>
        </div>

    </div>

</div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/mailer/endpoints/create.css') }}?v={{ @filemtime(public_path('managers/css/views/mailer/endpoints/create.css')) ?: '1' }}">
<link rel="stylesheet" href="{{ asset('managers/css/views/mailer/endpoints/variables.css') }}?v={{ @filemtime(public_path('managers/css/views/mailer/endpoints/variables.css')) ?: '1' }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/mailer/endpoints/create.js') }}?v={{ @filemtime(public_path('managers/js/views/mailer/endpoints/create.js')) ?: '1' }}"></script>
<script src="{{ asset('managers/js/views/mailer/endpoints/variables.js') }}?v={{ @filemtime(public_path('managers/js/views/mailer/endpoints/variables.js')) ?: '1' }}"></script>
@endpush
