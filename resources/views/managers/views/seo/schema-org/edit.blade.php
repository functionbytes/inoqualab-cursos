@extends('layouts.managers')

@section('title', 'Schema.org — ' . Str::limit($seoMeta->title ?? 'Meta #' . $seoMeta->id, 40))

@section('page_header')
    @include('managers.includes.card', ['title' => 'Schema.org — ' . Str::limit($seoMeta->title ?? 'Meta #' . $seoMeta->id, 40)])
@endsection

@section('content')


    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Inicio</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('manager.seo.metas.index') }}">SEO metas</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('manager.seo.metas.edit', $seoMeta) }}">Editar</a>
            </li>
            <li class="breadcrumb-item active">Schema.org</li>
        </ol>
    </nav>

    <div class="row g-3">

        {{-- Columna principal --}}
        <div class="col-12 col-lg-8">

            {{-- Info del meta --}}
            <div class="card mb-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-2 d-flex align-items-center justify-content-center bg-primary-subtle flex-shrink-0 icon-box-40">
                            <i class="fas fa-code text-primary"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <h6 class="fw-semibold mb-0 text-truncate">
                                {{ $seoMeta->title ?: ('Meta #' . $seoMeta->id) }}
                            </h6>
                            <p class="text-muted">
                                {{ class_basename($seoMeta->seoable_type) }} #{{ $seoMeta->seoable_id }}
                                @if($currentType)
                                    &mdash; <span class="badge bg-primary-subtle text-primary">{{ $currentType }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Formulario --}}
            <form method="POST" action="{{ route('manager.seo.schema-org.update', $seoMeta) }}" id="formSchema"
                  data-template-url-template="{{ route('manager.seo.schema-org.template', ':type') }}"
                  data-validate-url="{{ route('manager.seo.schema-org.validate') }}">
                @csrf
                @method('PUT')

                <div class="card">
                    <div class="card-header border-bottom p-3">
                        <h5 class="mb-0 fw-bold">Editor Schema.org</h5>
                        <p class="text-muted">Define el JSON-LD estructurado para esta página</p>
                    </div>

                    <div class="card-body">

                        {{-- Tipo de schema --}}
                        <div class="mb-3">
                            <label for="schema_type" class="form-label fw-semibold">Tipo de schema</label>
                            <select name="schema_type" id="schema_type" class="form-select">
                                <option value="">Sin schema</option>
                                @foreach($types as $type)
                                    <option value="{{ $type }}" @selected($currentType === $type)>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Al cambiar el tipo se cargará una plantilla base.</div>
                        </div>

                        {{-- Editor JSON --}}
                        <div class="mb-3">
                            <label for="schema_custom" class="form-label fw-semibold">JSON-LD personalizado</label>
                            <textarea name="schema_custom"
                                      id="schema_custom"
                                      class="form-control font-monospace"
                                      rows="15"
                                      {{-- @@context escapado: @context es una directiva real de Blade
                                           (CompilesContexts) y sin escapar abría un if sin @endcontext,
                                           dejando la vista con un error de sintaxis fatal. --}}
                                      placeholder='{"@@context":"https://schema.org","@@type":"Course","name":"..."}'>{{ $currentSchema ? json_encode($currentSchema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '' }}</textarea>
                            <div id="schema-validation-result" class="mt-2"></div>
                        </div>

                        {{-- Botón validar --}}
                        <div>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-validate-schema">
                                Validar JSON
                            </button>
                        </div>

                    </div>

                    <div class="card-footer bg-white">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            Guardar schema
                        </button>
                        <a href="{{ route('manager.seo.metas.edit', $seoMeta) }}"
                           class="btn btn-light w-100">
                            Cancelar
                        </a>
                    </div>

                </div>

            </form>

        </div>

        {{-- Panel lateral --}}
        <div class="col-12 col-lg-4">

            {{-- Tipos soportados --}}
            <div class="card">
                <div class="card-header border-bottom p-3">
                    <h6 class="mb-0 fw-bold">Tipos soportados</h6>
                    <p class="text-muted">Elige el que corresponda al contenido</p>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">

                        @php
                            $typeDescriptions = [
                                'Course'       => 'Para cursos online y programas de formación.',
                                'Article'      => 'Artículos de blog, noticias y contenido editorial.',
                                'Product'      => 'Productos con precio, disponibilidad y reseñas.',
                                'BreadcrumbList' => 'Ruta de navegación visible en resultados Google.',
                                'FAQPage'      => 'Preguntas frecuentes expandibles en SERPs.',
                                'Event'        => 'Eventos con fecha, lugar y organizador.',
                                'Person'       => 'Perfil de persona: autor, instructor, contacto.',
                                'Organization' => 'Datos de empresa u organización.',
                                'WebSite'      => 'Información general del sitio web.',
                                'VideoObject'  => 'Videos con duración, miniatura y descripción.',
                            ];
                        @endphp

                        @foreach($types as $type)
                            <li class="list-group-item px-3 py-2 d-flex align-items-start gap-2">
                                <span class="badge bg-primary-subtle text-primary mt-1 flex-shrink-0">{{ $type }}</span>
                                <p class="text-muted">
                                    {{ $typeDescriptions[$type] ?? 'Tipo de schema estructurado.' }}
                                </p>
                            </li>
                        @endforeach

                    </ul>
                </div>
            </div>

            {{-- Consejos --}}
            <div class="card mt-3">
                <div class="card-header border-bottom p-3">
                    <h6 class="mb-0 fw-bold">Buenas practicas</h6>
                </div>
                <div class="card-body p-3">
                    <ul class="list-unstyled mb-0 small text-muted">
                        <li class="mb-2">
                            <i class="fas fa-circle-check text-success me-1"></i>
                            El JSON debe ser un objeto válido
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-circle-check text-success me-1"></i>
                            Incluye siempre <code>@@context</code> y <code>@@type</code>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-circle-check text-success me-1"></i>
                            Valida en Google Rich Results Test
                        </li>
                        <li>
                            <i class="fas fa-circle-check text-success me-1"></i>
                            No añadas datos que no aparezcan en la página
                        </li>
                    </ul>
                </div>
            </div>

        </div>

    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/seo/schema-org/edit.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/seo/schema-org/edit.js') }}"></script>
@endpush

