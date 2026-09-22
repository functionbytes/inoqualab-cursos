@extends('layouts.managers')

@section('title', 'llms.txt')

@section('page_header')
    @include('managers.includes.card', ['title' => 'llms.txt'])
@endsection

@section('content')


    <div class="row g-4">

        {{-- Columna principal: editor --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header border-bottom p-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="mb-0 fw-bold">llms.txt</h5>
                            <p class="text-muted">
                                Describe tu sitio para motores de IA (ChatGPT, Claude, Perplexity, Gemini).
                                Formato Markdown con enlaces a las URLs más relevantes.
                            </p>
                        </div>
                        @if(isset($public_url))
                            <a href="{{ $public_url }}" target="_blank" rel="noopener"
                               class="badge bg-primary text-decoration-none d-flex align-items-center gap-1">
                                <i class="fas fa-external-link-alt"></i>
                                Ver archivo público
                            </a>
                        @endif
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="mb-3">
                        <label for="llms-editor" class="form-label fw-semibold">Contenido</label>
                        <small class="text-muted d-block mb-2">
                            Markdown simple. Un encabezado H1 con el nombre del sitio, un bloque
                            <code>&gt;</code> como resumen, y listas de enlaces por sección.
                        </small>
                        <textarea
                            id="llms-editor"
                            name="llms_txt"
                            class="form-control font-monospace llms-editor-textarea"
                            rows="20"
                            data-update-url="{{ route('manager.seo.llms.update') }}"
                            data-reset-url="{{ route('manager.seo.llms.reset') }}">{{ $content ?? '' }}</textarea>
                    </div>
                </div>

                <div class="card-footer bg-white border-top p-3">
                    <button type="button" class="btn btn-primary w-100 mb-2" id="btn-save-llms">
                        Guardar llms.txt
                    </button>
                    <button type="button" class="btn btn-outline-secondary w-100" id="btn-reset-llms">
                        Restaurar default
                    </button>
                </div>
            </div>
        </div>

        {{-- Columna lateral: guía --}}
        <div class="col-lg-4">

            {{-- URL pública --}}
            <div class="card mb-3">
                <div class="card-header border-bottom p-3">
                    <h6 class="mb-0 fw-bold">URL pública</h6>
                </div>
                <div class="card-body p-3">
                    <p class="text-muted small mb-2">Los motores de IA buscan el archivo en:</p>
                    @if(isset($public_url))
                        <a href="{{ $public_url }}" target="_blank" rel="noopener"
                           class="d-flex align-items-center gap-2 text-primary text-decoration-none small">
                            <i class="fas fa-link"></i>
                            <code class="text-primary">{{ $public_url }}</code>
                        </a>
                    @else
                        <code class="small">{{ url('/llms.txt') }}</code>
                    @endif
                </div>
            </div>

            {{-- Guía de formato --}}
            <div class="card mb-3">
                <div class="card-header border-bottom p-3">
                    <h6 class="mb-0 fw-bold">Formato Markdown para LLMs</h6>
                    <p class="text-muted">Estructura recomendada del archivo</p>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-3 py-2">
                            <div class="d-flex align-items-start gap-2">
                                <span class="badge bg-dark text-white flex-shrink-0 mt-1 badge-fs-10">H1</span>
                                <div>
                                    <code class="small"># Nombre del sitio</code>
                                    <div class="text-muted text-fs-11">Encabezado principal, nombre del proyecto o marca</div>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item px-3 py-2">
                            <div class="d-flex align-items-start gap-2">
                                <span class="badge bg-secondary text-white flex-shrink-0 mt-1 badge-fs-10">&gt;</span>
                                <div>
                                    <code class="small">&gt; Descripción corta</code>
                                    <div class="text-muted text-fs-11">Blockquote con resumen breve del sitio en una línea</div>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item px-3 py-2">
                            <div class="d-flex align-items-start gap-2">
                                <span class="badge bg-info text-white flex-shrink-0 mt-1 badge-fs-10">H2</span>
                                <div>
                                    <code class="small">## Secciones</code>
                                    <div class="text-muted text-fs-11">Agrupa los enlaces por categoría o área</div>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item px-3 py-2">
                            <div class="d-flex align-items-start gap-2">
                                <span class="badge bg-success text-white flex-shrink-0 mt-1 badge-fs-10">–</span>
                                <div>
                                    <code class="small">- [Enlace](url): descripción</code>
                                    <div class="text-muted text-fs-11">Lista de URLs relevantes con descripción breve</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Plantilla de ejemplo --}}
            <div class="card mb-3">
                <div class="card-header border-bottom p-3">
                    <h6 class="mb-0 fw-bold">Plantilla básica</h6>
                    <p class="text-muted">Copia y adapta este ejemplo</p>
                </div>
                <div class="card-body p-3">
                    <pre class="small bg-light p-2 rounded mb-0 llms-example-pre"># Nombre del sitio

&gt; Descripción breve en una línea.

## Contenido principal

- [Cursos]({{ url('/cursos') }}): Catálogo completo.
- [Blog]({{ url('/blog') }}): Artículos recientes.

## Soporte

- [Contacto]({{ url('/contacto') }}): Formulario de contacto.</pre>
                </div>
            </div>

            {{-- ¿Qué es llms.txt? --}}
            <div class="card">
                <div class="card-header border-bottom p-3">
                    <h6 class="mb-0 fw-bold">¿Qué es llms.txt?</h6>
                </div>
                <div class="card-body p-3">
                    <p class="text-muted small mb-2">
                        Estándar propuesto en 2024 para ayudar a modelos de lenguaje a entender el sitio.
                        Complementa <code>robots.txt</code>, no lo reemplaza.
                    </p>
                    <p class="text-muted small mb-0">
                        Especificación: <a href="https://llmstxt.org" target="_blank" rel="noopener">llmstxt.org</a>
                    </p>
                </div>
            </div>

        </div>

    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/seo/llms/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/seo/llms/index.js') }}"></script>
@endpush
