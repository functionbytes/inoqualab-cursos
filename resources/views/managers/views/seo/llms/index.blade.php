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
                <div class="card-header border-bottom">
                    <h6 class="mb-1 fw-bold">llms.txt</h6>
                    <p class="text-muted small mb-0">
                        Describe tu sitio para motores de IA (ChatGPT, Claude, Perplexity, Gemini).
                        Formato Markdown con enlaces a las URLs más relevantes.
                    </p>
                </div>

                <div class="card-body">
                    <label for="llms-editor" class="form-label fw-semibold">Contenido</label>
                    <p class="text-muted small mb-2">
                        Markdown simple. Un encabezado H1 con el nombre del sitio, un párrafo con
                        blockquote como resumen, y listas de enlaces por sección.
                    </p>
                    {{-- CodeMirror monta acá (mismo patrón que mail_templates/edit.blade.php),
                         el textarea real queda oculto y solo guarda el valor inicial/de fallback. --}}
                    <div id="llmsEditorWrapper" class="llms-editor-wrapper"></div>
                    <textarea
                        id="llms-editor"
                        name="llms_txt"
                        class="d-none"
                        data-update-url="{{ route('manager.seo.llms.update') }}"
                        data-reset-url="{{ route('manager.seo.llms.reset') }}">{{ $content ?? '' }}</textarea>
                </div>

                <div class="card-footer">
                    <button type="button" class="btn btn-llms-save w-100 mb-2" id="btn-save-llms">
                        Guardar
                    </button>
                    <button type="button" class="btn btn-primary w-100" id="btn-reset-llms">
                        Restaurar al default
                    </button>
                </div>
            </div>
        </div>

        {{-- Columna lateral: guía --}}
        <div class="col-lg-4">

            {{-- URL pública -- mismo patrón que la tarjeta homónima de
                 robots/index.blade.php (bloque de código de ancho completo,
                 sin ícono ni texto azul suelto), antes esta usaba un
                 tratamiento distinto (fila flex + ícono + texto/code en
                 azul) inconsistente con el resto del módulo SEO. --}}
            <div class="card mb-3">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">URL pública</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">Los motores de IA buscan el archivo en:</p>
                    <a href="{{ $public_url ?? url('/llms.txt') }}" target="_blank" rel="noopener" class="text-decoration-none">
                        <code class="d-block bg-light p-2 rounded small text-break text-dark">{{ $public_url ?? url('/llms.txt') }}</code>
                    </a>
                </div>
            </div>

            {{-- ¿Qué es llms.txt? --}}
            <div class="card mb-3">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">¿Qué es llms.txt?</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">
                        Estándar propuesto en 2024 para ayudar a modelos de lenguaje a entender el sitio.
                        Complementa <code>robots.txt</code>, no lo reemplaza.
                    </p>
                    <p class="text-muted small mb-0">
                        Especificación: <a href="https://llmstxt.org" target="_blank" rel="noopener">llmstxt.org</a>
                    </p>
                </div>
            </div>

            {{-- Plantilla de ejemplo --}}
            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Plantilla básica</h6>
                </div>
                <div class="card-body">
                    <pre class="small bg-light p-2 rounded mb-0 llms-example-pre"># Nombre del sitio

&gt; Descripción breve en una línea.

## Secciones

- [Docs]({{ url('/docs') }}): Guía para desarrolladores.
- [Blog]({{ url('/blog') }}): Artículos recientes.

## Opcional

- [Contacto]({{ url('/contacto') }})</pre>
                </div>
            </div>

        </div>

    </div>

@endsection

@push('css')
{{-- CodeMirror: mismo CDN que mail_templates/edit.blade.php. Solo lib +
     mode/markdown -- este editor es texto plano, no necesita los addons de
     HTML (closetag/matchbrackets/hint) que usa el editor de plantillas. --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/lib/codemirror.min.css">
<link rel="stylesheet" href="{{ asset('managers/css/views/seo/llms/index.css') }}?v={{ @filemtime(public_path('managers/css/views/seo/llms/index.css')) ?: 1 }}">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/lib/codemirror.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.2/mode/markdown/markdown.min.js"></script>
<script src="{{ asset('managers/js/views/seo/llms/index.js') }}?v={{ @filemtime(public_path('managers/js/views/seo/llms/index.js')) ?: 1 }}"></script>
@endpush
