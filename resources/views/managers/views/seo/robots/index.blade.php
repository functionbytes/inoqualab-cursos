@extends('layouts.managers')

@section('title', 'Robots.txt')

@section('page_header')
    @include('managers.includes.card', ['title' => 'Robots.txt'])
@endsection

@section('content')


    <div class="row g-4">

        {{-- Columna principal: editor --}}
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header border-bottom">
                    <h6 class="mb-1 fw-bold">Robots.txt</h6>
                    <p class="text-muted small mb-0">
                        Configura el archivo robots.txt que controla el acceso de los bots de los
                        motores de búsqueda a tu sitio.
                    </p>
                </div>

                <div class="card-body">
                    <label for="robots-editor" class="form-label fw-semibold">Contenido</label>
                    <p class="text-muted small mb-2">
                        Define las reglas de rastreo para los motores de búsqueda. Usa directivas
                        <code>User-agent</code>, <code>Allow</code>, <code>Disallow</code> y <code>Sitemap</code>.
                    </p>
                    {{-- CodeMirror monta acá (mismo patrón que seo/llms/index.blade.php), el
                         textarea real queda oculto y solo guarda el valor inicial/de fallback. --}}
                    <div id="robotsEditorWrapper" class="robots-editor-wrapper"></div>
                    <textarea
                        id="robots-editor"
                        name="robots_txt"
                        class="d-none"
                        data-update-url="{{ route('manager.seo.robots.update') }}"
                        data-reset-url="{{ route('manager.seo.robots.reset') }}"
                    >{{ $content }}</textarea>
                </div>

                <div class="card-footer">
                    <button type="button" class="btn btn-robots-save w-100 mb-2" id="btn-save-robots">
                        Guardar
                    </button>
                    <button type="button" class="btn btn-primary w-100" id="btn-reset-robots">
                        Restaurar al default
                    </button>
                </div>
            </div>

            {{-- Probar URL: verifica si una ruta queda bloqueada por las reglas
                 actuales del editor (no requiere guardar primero -- se evalúa
                 en el navegador contra el contenido en pantalla). --}}
            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-1 fw-bold">Probar URL</h6>
                    <p class="text-muted small mb-0">Comprueba si una URL está bloqueada por tu robots.txt actual.</p>
                </div>
                <div class="card-body">
                    <div class="input-group">
                        <input type="text" class="form-control" id="robots-test-url"
                               placeholder="https://tusitio.com/pagina-privada">
                        <button type="button" class="btn btn-robots-save" id="btn-test-url" aria-label="Probar">
                            <i class="fas fa-magnifying-glass"></i>
                        </button>
                    </div>
                    <div id="robots-test-result" class="small mt-2"></div>
                </div>
            </div>
        </div>

        {{-- Columna lateral --}}
        <div class="col-lg-4">

            {{-- Panel de estado --}}
            <div class="card mb-3">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Estado actual</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">User-agent</span>
                            <span class="small fw-bold">{{ $stats['user_agents'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Allow</span>
                            <span class="small fw-bold">{{ $stats['allow'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Disallow</span>
                            <span class="small fw-bold">{{ $stats['disallow'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Sitemap</span>
                            <span class="small fw-bold">{{ $stats['sitemaps'] }}</span>
                        </div>
                    </div>

                    <hr class="my-3">

                    <h6 class="mb-1 fw-bold">URL pública</h6>
                    <p class="text-muted small mb-2">El archivo se sirve en:</p>
                    <a href="{{ $public_url }}" target="_blank" rel="noopener" class="text-decoration-none">
                        <code class="d-block bg-light p-2 rounded small text-break text-dark">{{ $public_url }}</code>
                    </a>
                </div>
            </div>

            {{-- Panel de directivas comunes --}}
            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Directivas comunes</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Haz clic en "Insertar" para añadir al editor.</p>

                    {{-- Bloquear todo --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-semibold text-muted">Bloquear todo</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-insert-snippet"
                                    data-snippet="User-agent: *&#10;Disallow: /">
                                Insertar
                            </button>
                        </div>
                        <pre class="small bg-light p-2 rounded mb-0 robots-snippet-pre">User-agent: *
Disallow: /</pre>
                    </div>

                    {{-- Permitir todo --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-semibold text-muted">Permitir todo</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-insert-snippet"
                                    data-snippet="User-agent: *&#10;Allow: /">
                                Insertar
                            </button>
                        </div>
                        <pre class="small bg-light p-2 rounded mb-0 robots-snippet-pre">User-agent: *
Allow: /</pre>
                    </div>

                    {{-- Bloquear panel --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-semibold text-muted">Bloquear panel</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-insert-snippet"
                                    data-snippet="Disallow: /panel/&#10;Disallow: /manager/">
                                Insertar
                            </button>
                        </div>
                        <pre class="small bg-light p-2 rounded mb-0 robots-snippet-pre">Disallow: /panel/
Disallow: /manager/</pre>
                    </div>

                    {{-- Permitir bots IA --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-semibold text-muted">Permitir bots IA</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-insert-snippet"
                                    data-snippet="User-agent: GPTBot&#10;Allow: /&#10;&#10;User-agent: ClaudeBot&#10;Allow: /">
                                Insertar
                            </button>
                        </div>
                        <pre class="small bg-light p-2 rounded mb-0 robots-snippet-pre">User-agent: GPTBot
Allow: /

User-agent: ClaudeBot
Allow: /</pre>
                    </div>

                    <div class="bg-light border rounded p-2 small mb-0">
                        <strong>Precaución:</strong> los cambios afectan a cómo los motores de búsqueda rastrean tu sitio. Revisa antes de guardar.
                    </div>
                </div>
            </div>

        </div>

    </div>

@endsection

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/lib/codemirror.min.css">
<link rel="stylesheet" href="{{ asset('managers/css/views/seo/robots/index.css') }}?v={{ @filemtime(public_path('managers/css/views/seo/robots/index.css')) ?: 1 }}">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/lib/codemirror.min.js"></script>
<script src="{{ asset('managers/js/views/seo/robots/index.js') }}?v={{ @filemtime(public_path('managers/js/views/seo/robots/index.js')) ?: 1 }}"></script>
@endpush
