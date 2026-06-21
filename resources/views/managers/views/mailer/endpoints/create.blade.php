@extends('layouts.managers')

@section('title', 'Crear email endpoint')

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

<div class="card">
    <form method="POST" action="{{ route('mailers.endpoints.store') }}" id="formCreate">
        @csrf

        <div class="card-header border-bottom p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-bold">Crear nuevo endpoint de email</h5>
                    <p class="text-muted mb-0 mt-1">Configura un endpoint para recibir solicitudes de envío de emails desde sistemas externos.</p>
                </div>
                <a href="{{ route('mailers.endpoints.index') }}" class="btn btn-light">Atrás</a>
            </div>
        </div>

        <div class="card-body">
            <div class="row g-3">

                {{-- Información básica --}}
                <div class="col-12">
                    <h6 class="fw-bold mb-1 border-bottom pb-2">Información básica</h6>
                    <p class="text-muted small mb-3">Define el nombre, slug único y la fuente del endpoint. El slug se usará en la URL de la API.</p>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">Nombre del endpoint <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name') }}"
                           placeholder="Ej: PrestaShop Password Reset" required maxlength="255">
                    <small class="form-text text-muted">Nombre descriptivo para identificar este endpoint</small>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">Slug (único) <span class="text-danger">*</span></label>
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
                    <label class="form-label">Fuente (sistema) <span class="text-danger">*</span></label>
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
                    <label class="form-label">Tipo de correo <span class="text-danger">*</span></label>
                    <select class="form-select select2 @error('type') is-invalid @enderror" id="type" name="type" required>
                        <option value="">Seleccionar tipo...</option>
                        <option value="transactional" @if(old('type') === 'transactional') selected @endif>Transactional</option>
                        <option value="notification" @if(old('type') === 'notification') selected @endif>Notification</option>
                    </select>
                    <small class="form-text text-muted">Categoría del email (para organización)</small>
                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Descripción</label>
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

                {{-- Plantilla --}}
                <div class="col-12">
                    <hr>
                    <h6 class="fw-bold mb-1 border-bottom pb-2">Plantilla de email</h6>
                    <p class="text-muted small mb-3">Selecciona la plantilla de email que se usará para los envíos.</p>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">Plantilla de email</label>
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

                {{-- Variables esperadas --}}
                <div class="col-12">
                    <hr>
                    <h6 class="fw-bold mb-1 border-bottom pb-2">Variables esperadas</h6>
                    <p class="text-muted small mb-3">Define las variables que esperas recibir en el JSON desde el sistema externo.</p>
                </div>

                <div class="col-12">
                    <div class="alert alert-light border mb-3">
                        <i class="fas fa-lightbulb me-2 text-warning"></i>
                        <strong>Ejemplo:</strong> Si tu JSON envía <code>{"customer_email": "user@example.com"}</code>, agrega <code>customer_email</code> como variable.
                    </div>
                    <div id="expectedVariablesContainer" class="mb-3"></div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="addExpectedVariable">
                        <i class="fas fa-plus me-2"></i>Agregar variable
                    </button>
                </div>

                {{-- Variables obligatorias --}}
                <div class="col-12">
                    <hr>
                    <h6 class="fw-bold mb-1 border-bottom pb-2">Variables obligatorias</h6>
                    <p class="text-muted small mb-3">Marca las variables que son obligatorias en cada request.</p>
                </div>

                <div class="col-12">
                    <div id="requiredVariablesContainer">
                        <p class="text-muted"><i class="fas fa-info-circle me-1"></i>Primero agrega variables esperadas arriba</p>
                    </div>
                </div>

                {{-- Mapeo de variables --}}
                <div class="col-12">
                    <hr>
                    <h6 class="fw-bold mb-1 border-bottom pb-2">Mapeo de variables (opcional)</h6>
                    <p class="text-muted small mb-3">Si los nombres en el JSON no coinciden con los de la plantilla, puedes mapearlos aquí.</p>
                </div>

                <div class="col-12">
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
                            <i class="fas fa-plus me-2"></i>Agregar mapeo
                        </button>
                    </div>
                </div>

                {{-- Info útil --}}
                <div class="col-12">
                    <hr>
                    <div class="alert alert-info border-0">
                        <h6 class="alert-heading fw-semibold"><i class="fas fa-graduation-cap me-2"></i>Información útil</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="mb-0 small">
                                    <li>El <strong>token API</strong> se genera automáticamente al crear el endpoint</li>
                                    <li>Podrás ver <strong>estadísticas</strong> y <strong>logs</strong> de uso</li>
                                    <li>Los endpoints inactivos <strong>rechazarán</strong> todas las peticiones</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <p class="small mb-1"><strong>Ejemplo de request:</strong></p>
                                <pre class="bg-dark text-light p-2 rounded mb-0" style="font-size:10px;"><code>POST /api/email-endpoints/slug/send
Header: X-API-Token: abc123...
{"email": "user@example.com"}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="card-footer border-top">
            <button type="submit" class="btn btn-primary w-100 mb-1">Crear endpoint</button>
            <a href="{{ route('mailers.endpoints.index') }}" class="btn btn-light w-100">Cancelar</a>
        </div>

    </form>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/modules/mailer-endpoint-edit.js') }}"></script>
<script>
$(document).ready(function() {
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({ allowClear: false, width: '100%' });
    }

    if (typeof MailerEndpointEdit !== 'undefined') {
        MailerEndpointEdit.init();
    }

    // Slug preview
    $('#slug').on('input', function() {
        $('#slugPreview').text($(this).val() || 'slug');
    });

    // Add expected variable
    $('#addExpectedVariable').on('click', function() {
        const html = `<div class="input-group mb-2">
            <span class="input-group-text bg-light"><i class="fas fa-cube text-primary"></i></span>
            <input type="text" class="form-control expected-var-input" name="expected_variables[]" placeholder="Ej: customer_email">
            <button type="button" class="btn btn-outline-danger remove-variable"><i class="fas fa-times"></i></button>
        </div>`;
        $('#expectedVariablesContainer').append(html);
        updateRequiredVariables();
    });

    // Remove expected variable
    $(document).on('click', '.remove-variable', function() {
        $(this).closest('.input-group').remove();
        updateRequiredVariables();
    });

    // Update required variables checkboxes
    function updateRequiredVariables() {
        const vars = [];
        $('.expected-var-input').each(function() {
            const v = $(this).val().trim();
            if (v) vars.push(v);
        });

        if (vars.length === 0) {
            $('#requiredVariablesContainer').html('<p class="text-muted"><i class="fas fa-info-circle me-1"></i>Primero agrega variables esperadas arriba</p>');
            return;
        }

        let html = '';
        vars.forEach(function(v, i) {
            html += `<div class="form-check form-check-inline mb-2">
                <input class="form-check-input" type="checkbox" name="required_variables[]" id="req_${i}" value="${v}">
                <label class="form-check-label" for="req_${i}">
                    <span class="badge bg-light text-dark border rounded-pill py-1 px-2">${v}</span>
                </label>
            </div>`;
        });
        $('#requiredVariablesContainer').html(html);
    }

    $(document).on('input', '.expected-var-input', updateRequiredVariables);

    // Add mapping
    $('#addMapping').on('click', function() {
        const html = `<div class="row g-2 mb-2 mapping-row">
            <div class="col-5"><input type="text" class="form-control form-control-sm mapping-template" placeholder="email"></div>
            <div class="col-5"><input type="text" class="form-control form-control-sm mapping-json" placeholder="user.email"></div>
            <div class="col-2"><button type="button" class="btn btn-sm btn-outline-danger remove-mapping w-100"><i class="fas fa-times"></i></button></div>
        </div>`;
        $('#mappingsContainer').append(html);
    });

    $(document).on('click', '.remove-mapping', function() {
        $(this).closest('.mapping-row').remove();
    });

    // Serialize mappings before submit
    $('#formCreate').on('submit', function() {
        $('.mapping-row').each(function() {
            const tplVar = $(this).find('.mapping-template').val().trim();
            const jsonPath = $(this).find('.mapping-json').val().trim();
            if (tplVar && jsonPath) {
                $(this).append(`<input type="hidden" name="variable_mappings[${tplVar}]" value="${jsonPath}">`);
            }
        });
    });
});
</script>
@endpush
