@extends('layouts.managers')

@section('title', 'Robots.txt')

@section('page_header')
    @include('managers.includes.card', ['title' => 'Robots.txt'])
@endsection

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
                            data-update-url="{{ route('manager.seo.robots.update') }}"
                            data-reset-url="{{ route('manager.seo.robots.reset') }}"
                        >{{ $content }}</textarea>
                    </div>
                </div>

                <div class="card-footer bg-white border-top">
                    <button type="button" class="btn btn-primary w-100 mb-2" id="btn-save-robots">
                        Guardar robots.txt
                    </button>
                    <button type="button" class="btn btn-outline-secondary w-100" id="btn-reset-robots">
                        Restaurar default
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
                            <span class="badge bg-primary-subtle text-primary rounded-pill">{{ $stats['user_agents'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Allow</span>
                            <span class="badge bg-success-subtle text-success rounded-pill">{{ $stats['allow'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Disallow</span>
                            <span class="badge bg-danger-subtle text-danger rounded-pill">{{ $stats['disallow'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Sitemap</span>
                            <span class="badge bg-info-subtle text-info rounded-pill">{{ $stats['sitemaps'] }}</span>
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

                    <div class="alert alert-warning small mb-0">
                        <i class="fas fa-triangle-exclamation me-1"></i>
                        <strong>Precaución:</strong> los cambios afectan a cómo los motores de búsqueda rastrean tu sitio. Revisa antes de guardar.
                    </div>
                </div>
            </div>

        </div>

    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/seo/robots/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/seo/robots/index.js') }}"></script>
@endpush
