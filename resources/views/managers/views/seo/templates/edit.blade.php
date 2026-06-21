@extends('layouts.managers')

@section('content')


    <div class="row g-3">

        {{-- Form column --}}
        <div class="col-12 col-lg-8">
            <div class="card">
                <form id="templateForm" action="{{ route('manager.seo.templates.update', $seoTemplate) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card-header border-bottom p-3">
                        <h5 class="mb-0 fw-bold">Editar plantilla SEO</h5>
                        <p class="text-muted">Modifica los patrones y configuración de esta plantilla.</p>
                    </div>

                    <div class="card-body">

                        <h6 class="fw-bold mb-3 border-bottom pb-2">Informacion basica</h6>

                        <div class="row g-3 mb-4">

                            <div class="col-12">
                                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text"
                                       name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $seoTemplate->name) }}"
                                       placeholder="ej: Plantilla para cursos"
                                       required>
                                @error('name')
                                    <span class="field-validation-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Tipo de modelo</label>
                                <select name="model_type" class="form-select select2 @error('model_type') is-invalid @enderror">
                                    <option value="">Global (todos los modelos)</option>
                                    <option value="App\Models\Course" {{ old('model_type', $seoTemplate->model_type) === 'App\\Models\\Course' ? 'selected' : '' }}>Curso</option>
                                    <option value="App\Models\User" {{ old('model_type', $seoTemplate->model_type) === 'App\\Models\\User' ? 'selected' : '' }}>Usuario</option>
                                </select>
                                <small class="form-text text-muted">Deja en blanco para aplicar globalmente.</small>
                                @error('model_type')
                                    <span class="field-validation-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Estado</label>
                                <select name="is_active" class="form-select select2">
                                    <option value="1" {{ old('is_active', $seoTemplate->is_active ? '1' : '0') === '1' ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ old('is_active', $seoTemplate->is_active ? '1' : '0') === '0' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label">Prioridad</label>
                                <input type="number"
                                       name="priority"
                                       class="form-control @error('priority') is-invalid @enderror"
                                       value="{{ old('priority', $seoTemplate->priority) }}"
                                       min="0"
                                       max="255">
                                <small class="form-text text-muted">Mayor número = mayor prioridad (0–255).</small>
                                @error('priority')
                                    <span class="field-validation-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</span>
                                @enderror
                            </div>

                        </div>

                        <h6 class="fw-bold mb-3 border-bottom pb-2">Patrones de contenido</h6>

                        <div class="row g-3 mb-4">

                            <div class="col-12">
                                <label class="form-label">Patrón para título</label>
                                <input type="text"
                                       name="title_pattern"
                                       class="form-control @error('title_pattern') is-invalid @enderror"
                                       value="{{ old('title_pattern', $seoTemplate->title_pattern) }}"
                                       maxlength="200"
                                       placeholder="ej: {title} | {site_name}">
                                <small class="form-text text-muted">Usa variables entre llaves: <code>{title}</code>, <code>{name}</code>, <code>{site_name}</code>.</small>
                                @error('title_pattern')
                                    <span class="field-validation-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Patrón para descripción</label>
                                <textarea name="description_pattern"
                                          class="form-control @error('description_pattern') is-invalid @enderror"
                                          rows="3"
                                          maxlength="500"
                                          placeholder="ej: {description} — Aprende con {site_name}">{{ old('description_pattern', $seoTemplate->description_pattern) }}</textarea>
                                <small class="form-text text-muted">Máximo 500 caracteres.</small>
                                @error('description_pattern')
                                    <span class="field-validation-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</span>
                                @enderror
                            </div>

                        </div>

                        <h6 class="fw-bold mb-3 border-bottom pb-2">Configuracion social y robots</h6>

                        <div class="row g-3">

                            <div class="col-12 col-md-4">
                                <label class="form-label">Tipo OG</label>
                                <select name="og_type" class="form-select select2 @error('og_type') is-invalid @enderror">
                                    <option value="">Sin definir</option>
                                    <option value="website" {{ old('og_type', $seoTemplate->og_type) === 'website' ? 'selected' : '' }}>website</option>
                                    <option value="article" {{ old('og_type', $seoTemplate->og_type) === 'article' ? 'selected' : '' }}>article</option>
                                    <option value="product" {{ old('og_type', $seoTemplate->og_type) === 'product' ? 'selected' : '' }}>product</option>
                                </select>
                                @error('og_type')
                                    <span class="field-validation-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label">Twitter card</label>
                                <select name="twitter_card" class="form-select select2 @error('twitter_card') is-invalid @enderror">
                                    <option value="">Sin definir</option>
                                    <option value="summary" {{ old('twitter_card', $seoTemplate->twitter_card) === 'summary' ? 'selected' : '' }}>summary</option>
                                    <option value="summary_large_image" {{ old('twitter_card', $seoTemplate->twitter_card) === 'summary_large_image' ? 'selected' : '' }}>summary_large_image</option>
                                </select>
                                @error('twitter_card')
                                    <span class="field-validation-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label">Robots</label>
                                <select name="robots" class="form-select select2 @error('robots') is-invalid @enderror">
                                    <option value="">Sin definir</option>
                                    <option value="index,follow" {{ old('robots', $seoTemplate->robots) === 'index,follow' ? 'selected' : '' }}>index, follow</option>
                                    <option value="noindex,follow" {{ old('robots', $seoTemplate->robots) === 'noindex,follow' ? 'selected' : '' }}>noindex, follow</option>
                                    <option value="index,nofollow" {{ old('robots', $seoTemplate->robots) === 'index,nofollow' ? 'selected' : '' }}>index, nofollow</option>
                                    <option value="noindex,nofollow" {{ old('robots', $seoTemplate->robots) === 'noindex,nofollow' ? 'selected' : '' }}>noindex, nofollow</option>
                                </select>
                                @error('robots')
                                    <span class="field-validation-error"><i class="fas fa-circle-exclamation"></i> {{ $message }}</span>
                                @enderror
                            </div>

                        </div>

                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100 mb-1">Guardar cambios</button>
                        <a href="{{ route('manager.seo.templates.index') }}" class="btn btn-light w-100">Cancelar</a>
                    </div>

                </form>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">

            {{-- Apply to metas --}}
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="card-title fw-bold mb-1">Aplicar plantilla</h6>
                    <p class="text-muted small mb-3">Aplica los patrones de esta plantilla a los registros de meta SEO existentes.</p>
                    <button type="button" class="btn btn-outline-primary w-100 apply-sidebar-btn"
                            data-preview-url="{{ route('manager.seo.templates.preview', $seoTemplate) }}"
                            data-apply-url="{{ route('manager.seo.templates.bulk-apply', $seoTemplate) }}"
                            data-id="{{ $seoTemplate->id }}">
                        Aplicar a metas
                    </button>
                </div>
            </div>

            {{-- Variables help --}}
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title fw-bold mb-3">Variables disponibles</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex align-items-start gap-2 mb-2">
                            <code class="bg-light px-2 py-1 rounded flex-shrink-0">{title}</code>
                            <span class="small text-muted">Título del registro</span>
                        </li>
                        <li class="d-flex align-items-start gap-2 mb-2">
                            <code class="bg-light px-2 py-1 rounded flex-shrink-0">{name}</code>
                            <span class="small text-muted">Nombre del registro</span>
                        </li>
                        <li class="d-flex align-items-start gap-2 mb-2">
                            <code class="bg-light px-2 py-1 rounded flex-shrink-0">{description}</code>
                            <span class="small text-muted">Descripción del registro</span>
                        </li>
                        <li class="d-flex align-items-start gap-2 mb-2">
                            <code class="bg-light px-2 py-1 rounded flex-shrink-0">{site_name}</code>
                            <span class="small text-muted">Nombre del sitio</span>
                        </li>
                        <li class="d-flex align-items-start gap-2 mb-2">
                            <code class="bg-light px-2 py-1 rounded flex-shrink-0">{year}</code>
                            <span class="small text-muted">Año actual</span>
                        </li>
                        <li class="d-flex align-items-start gap-2 mb-0">
                            <code class="bg-light px-2 py-1 rounded flex-shrink-0">{month}</code>
                            <span class="small text-muted">Mes actual</span>
                        </li>
                    </ul>
                </div>
            </div>

        </div>

    </div>

    {{-- Apply modal --}}
    <div class="modal fade" id="applyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Aplicar plantilla a metas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center" id="apply-modal-body">
                    <div class="py-3">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted small">Calculando registros afectados...</p>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button type="button" class="btn btn-primary w-100 mb-2" id="confirm-apply-btn" disabled>Aplicar</button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {

    $('.select2').select2({ width: '100%' });

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    let currentApplyData = null;

    function openApplyModal(previewUrl, applyUrl, templateId) {
        currentApplyData = { applyUrl, templateId };

        $('#apply-modal-body').html(`
            <div class="py-3">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted small">Calculando registros afectados...</p>
            </div>
        `);
        $('#confirm-apply-btn').prop('disabled', true).text('Aplicar');
        $('#applyModal').modal('show');

        $.getJSON(previewUrl, function (res) {
            const count = res.affected_count ?? 0;
            $('#apply-modal-body').html(`
                <div class="display-4 text-primary mb-3"><i class="fas fa-layer-group"></i></div>
                <p class="mb-1">Esta plantilla se aplicará a</p>
                <h3 class="fw-bold mb-1">${count}</h3>
                <p class="text-muted small mb-0">registro(s) de meta SEO.</p>
            `);
            $('#confirm-apply-btn').prop('disabled', count === 0);
        }).fail(function () {
            $('#apply-modal-body').html('<p class="text-danger py-3">Error al cargar la previsualización.</p>');
        });
    }

    // Sidebar apply button
    $('.apply-sidebar-btn').on('click', function () {
        openApplyModal($(this).data('preview-url'), $(this).data('apply-url'), $(this).data('id'));
    });

    // Confirm apply
    $('#confirm-apply-btn').on('click', function () {
        if (!currentApplyData) return;
        const $btn = $(this);
        $btn.prop('disabled', true).text('Aplicando...');

        $.ajax({
            url: currentApplyData.applyUrl,
            method: 'POST',
            data: JSON.stringify({ template_id: currentApplyData.templateId }),
            contentType: 'application/json',
            success: function (res) {
                $('#applyModal').modal('hide');
                toastr.success(res.message || 'Plantilla aplicada correctamente.');
                currentApplyData = null;
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? 'Error al aplicar la plantilla.');
                $btn.prop('disabled', false).text('Aplicar');
            }
        });
    });

    $('#applyModal').on('hidden.bs.modal', function () {
        currentApplyData = null;
        $('#confirm-apply-btn').text('Aplicar');
    });

    @if(session('success'))
        toastr.success('{{ session("success") }}');
    @endif

});
</script>
@endpush
