@extends('layouts.managers')

@section('title', 'Robots.txt')

@section('content')


    <div class="row g-4">

        {{-- Columna principal: editor --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header border-bottom p-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="mb-0 fw-bold">Editor de robots.txt</h5>
                            <p class="text-muted">Controla el acceso de los bots de búsqueda a tu sitio</p>
                        </div>
                        <a href="{{ $public_url }}" target="_blank" class="badge bg-primary text-decoration-none">
                            <i class="fas fa-external-link-alt me-1"></i>Ver archivo público
                        </a>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="mb-3">
                        <label for="robots-editor" class="form-label fw-semibold">Contenido</label>
                        <p class="text-muted small mb-2">
                            Define las reglas de rastreo para los motores de búsqueda. Usa directivas
                            <code>User-agent</code>, <code>Allow</code>, <code>Disallow</code> y <code>Sitemap</code>.
                        </p>
                        <textarea
                            id="robots-editor"
                            name="robots_txt"
                            class="form-control font-monospace"
                            rows="20"
                        >{{ $content }}</textarea>
                    </div>
                </div>

                <div class="card-footer bg-white border-top">
                    <button type="button" class="btn btn-primary w-100 mb-2" id="btn-save-robots">
                        <i class="fas fa-save me-1"></i>Guardar robots.txt
                    </button>
                    <button type="button" class="btn btn-outline-secondary w-100" id="btn-reset-robots">
                        <i class="fas fa-rotate-left me-1"></i>Restaurar default
                    </button>
                </div>
            </div>
        </div>

        {{-- Columna lateral --}}
        <div class="col-lg-4">

            {{-- Panel de estado --}}
            <div class="card mb-3">
                <div class="card-header border-bottom p-3">
                    <h6 class="mb-0 fw-bold">Estado actual</h6>
                    <p class="text-muted">Resumen de directivas detectadas</p>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">User-agent</span>
                            <span class="badge bg-primary rounded-pill">{{ $stats['user_agents'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Allow</span>
                            <span class="badge bg-success rounded-pill">{{ $stats['allow'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Disallow</span>
                            <span class="badge bg-danger rounded-pill">{{ $stats['disallow'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Sitemap</span>
                            <span class="badge bg-info rounded-pill">{{ $stats['sitemaps'] }}</span>
                        </div>
                    </div>

                    <hr class="my-3">

                    <p class="text-muted small mb-1">URL pública del archivo:</p>
                    <a href="{{ $public_url }}" target="_blank" class="small text-break">
                        <code>{{ $public_url }}</code>
                    </a>
                </div>
            </div>

            {{-- Panel de directivas comunes --}}
            <div class="card">
                <div class="card-header border-bottom p-3">
                    <h6 class="mb-0 fw-bold">Directivas comunes</h6>
                    <p class="text-muted">Haz clic en "Insertar" para añadir al editor</p>
                </div>
                <div class="card-body p-3">

                    {{-- Bloquear todo --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-semibold text-muted">Bloquear todo</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-insert-snippet"
                                    data-snippet="User-agent: *&#10;Disallow: /">
                                <i class="fas fa-plus me-1"></i>Insertar
                            </button>
                        </div>
                        <pre class="small bg-light p-2 rounded mb-0" style="font-size:0.78rem;">User-agent: *
Disallow: /</pre>
                    </div>

                    {{-- Permitir todo --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-semibold text-muted">Permitir todo</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-insert-snippet"
                                    data-snippet="User-agent: *&#10;Allow: /">
                                <i class="fas fa-plus me-1"></i>Insertar
                            </button>
                        </div>
                        <pre class="small bg-light p-2 rounded mb-0" style="font-size:0.78rem;">User-agent: *
Allow: /</pre>
                    </div>

                    {{-- Bloquear panel --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-semibold text-muted">Bloquear panel</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-insert-snippet"
                                    data-snippet="Disallow: /panel/&#10;Disallow: /manager/">
                                <i class="fas fa-plus me-1"></i>Insertar
                            </button>
                        </div>
                        <pre class="small bg-light p-2 rounded mb-0" style="font-size:0.78rem;">Disallow: /panel/
Disallow: /manager/</pre>
                    </div>

                    {{-- Permitir bots IA --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-semibold text-muted">Permitir bots IA</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-insert-snippet"
                                    data-snippet="User-agent: GPTBot&#10;Allow: /&#10;&#10;User-agent: ClaudeBot&#10;Allow: /">
                                <i class="fas fa-plus me-1"></i>Insertar
                            </button>
                        </div>
                        <pre class="small bg-light p-2 rounded mb-0" style="font-size:0.78rem;">User-agent: GPTBot
Allow: /

User-agent: ClaudeBot
Allow: /</pre>
                    </div>

                    <div class="alert alert-warning small mb-0">
                        <i class="fas fa-triangle-exclamation me-1"></i>
                        <strong>Precaución:</strong> los cambios afectan a cómo los motores de búsqueda rastrean tu sitio. Revisa antes de guardar.
                    </div>
                </div>
            </div>

        </div>

    </div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {

    var $editor = $('#robots-editor');

    // Función para insertar snippet al final del textarea
    function insertSnippet(snippet) {
        var current = $editor.val().trimEnd();
        var separator = current.length > 0 ? '\n\n' : '';
        $editor.val(current + separator + snippet);
        $editor.focus();
        $editor[0].scrollTop = $editor[0].scrollHeight;
    }

    // Botones de insertar snippet
    $(document).on('click', '.btn-insert-snippet', function () {
        var raw = $(this).data('snippet');
        // data() already decodes HTML entities in some cases; ensure newlines
        var snippet = String(raw).replace(/&#10;/g, '\n');
        insertSnippet(snippet);
    });

    // Guardar robots.txt via AJAX
    $('#btn-save-robots').on('click', function () {
        var $btn = $(this).prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...'
        );

        $.ajax({
            url: '{{ route('manager.seo.robots.update') }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { robots_txt: $editor.val() },
            dataType: 'json',
            success: function (response) {
                toastr.success(response.message ?? 'robots.txt guardado correctamente');
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    var first = errors[Object.keys(errors)[0]];
                    toastr.error(first ? first[0] : 'Error de validación');
                } else {
                    toastr.error('Error al guardar el robots.txt');
                }
            },
            complete: function () {
                $btn.prop('disabled', false).html(
                    '<i class="fas fa-save me-1"></i>Guardar robots.txt'
                );
            }
        });
    });

    // Restaurar default via AJAX
    $('#btn-reset-robots').on('click', function () {
        if (!window.confirm('¿Restaurar el robots.txt al valor por defecto? Esta acción no se puede deshacer.')) {
            return;
        }

        var $btn = $(this).prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-1"></span>Restaurando...'
        );

        $.ajax({
            url: '{{ route('manager.seo.robots.reset') }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            dataType: 'json',
            success: function (response) {
                if (response.content !== undefined) {
                    $editor.val(response.content);
                }
                toastr.success(response.message ?? 'robots.txt restaurado al valor por defecto');
            },
            error: function () {
                toastr.error('Error al restaurar el robots.txt');
            },
            complete: function () {
                $btn.prop('disabled', false).html(
                    '<i class="fas fa-rotate-left me-1"></i>Restaurar default'
                );
            }
        });
    });

});
</script>
@endpush
