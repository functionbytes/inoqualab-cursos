@extends('layouts.managers')

@section('title', 'Editar SEO')

@section('page_header')
    @include('managers.includes.card', ['title' => 'Editar SEO'])
@endsection

@section('content')

    <div class="row g-3">

        {{-- Columna principal --}}
        <div class="col-12 col-lg-8">
            <form id="formSeoMeta" data-update-url="{{ route('manager.seo.metas.update', $seoMeta->id) }}">

                {{-- Secciones por pestañas en vez de acordeón: una sola
                     sección visible a la vez, sin scroll de acordeón
                     abriendo/cerrando. Estilo neutro (texto gris/negro, sin
                     acento de color) -- antes esta vista usaba
                     .user-profile-tab (pastilla gris en la activa),
                     inconsistente con el resto de la sección SEO. El fix
                     real va en edit.css (#seoMetaTab): .nav-tabs por si solo
                     no alcanza, theme.css redefine --bs-nav-tabs-link-active-bg
                     a #081A28 (mismo bug documentado en badge-colors.css). --}}
                <div class="card mb-3">

                    <div class="card-header p-4 border-bottom">
                        <h5 class="mb-1 fw-bold">Metadatos SEO</h5>
                        <p class="small mb-0 text-muted">Título, descripción, Open Graph, Twitter y datos técnicos de esta página.</p>
                    </div>

                    <ul class="nav nav-tabs border-0" id="seoMetaTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-0 py-3 active" id="tab-basico" data-bs-toggle="pill"
                                    data-bs-target="#pane-basico" type="button" role="tab"
                                    aria-controls="pane-basico" aria-selected="true">
                                Básico
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-0 py-3" id="tab-og" data-bs-toggle="pill"
                                    data-bs-target="#pane-og" type="button" role="tab"
                                    aria-controls="pane-og" aria-selected="false">
                                Open Graph
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-0 py-3" id="tab-twitter" data-bs-toggle="pill"
                                    data-bs-target="#pane-twitter" type="button" role="tab"
                                    aria-controls="pane-twitter" aria-selected="false">
                                Twitter / X
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-0 py-3" id="tab-tecnico" data-bs-toggle="pill"
                                    data-bs-target="#pane-tecnico" type="button" role="tab"
                                    aria-controls="pane-tecnico" aria-selected="false">
                                Técnico
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-0 py-3" id="tab-schema" data-bs-toggle="pill"
                                    data-bs-target="#pane-schema" type="button" role="tab"
                                    aria-controls="pane-schema" aria-selected="false">
                                Schema.org
                            </button>
                        </li>
                    </ul>

                    <div class="card-body">
                        <div class="tab-content" id="seoMetaTabContent">

                            {{-- Básico --}}
                            <div role="tabpanel" class="tab-pane fade show active" id="pane-basico" aria-labelledby="tab-basico">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Title
                                            <small class="text-muted fw-normal">(max 70)</small>
                                        </label>
                                        <input type="text" class="form-control" id="title" name="title"
                                               maxlength="70"
                                               value="{{ old('title', $seoMeta->title) }}"
                                               placeholder="Título SEO de la página">
                                        <div class="d-flex justify-content-between mt-1">
                                            <div class="invalid-feedback"></div>
                                            <small class="text-muted ms-auto">
                                                <span id="title-count">0</span>/70
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Description
                                            <small class="text-muted fw-normal">(max 170)</small>
                                        </label>
                                        <textarea class="form-control" id="description" name="description"
                                                  rows="3" maxlength="170"
                                                  placeholder="Descripción meta de la página">{{ old('description', $seoMeta->description) }}</textarea>
                                        <div class="d-flex justify-content-between mt-1">
                                            <div class="invalid-feedback"></div>
                                            <small class="text-muted ms-auto">
                                                <span id="description-count">0</span>/170
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Keywords</label>
                                        <input type="text" class="form-control" id="keywords" name="keywords"
                                               maxlength="500"
                                               value="{{ old('keywords', $seoMeta->keywords) }}"
                                               placeholder="palabra1, palabra2, palabra3">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Palabra clave principal</label>
                                        <input type="text" class="form-control" id="target_keyword" name="target_keyword"
                                               value="{{ old('target_keyword', $seoMeta->target_keyword) }}"
                                               placeholder="La keyword más importante de la página">
                                    </div>
                                </div>
                            </div>

                            {{-- Open Graph --}}
                            <div role="tabpanel" class="tab-pane fade" id="pane-og" aria-labelledby="tab-og">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">OG Title
                                            <small class="text-muted fw-normal">(max 95)</small>
                                        </label>
                                        <input type="text" class="form-control" name="og_title"
                                               maxlength="95"
                                               value="{{ old('og_title', $seoMeta->og_title) }}"
                                               placeholder="Título para redes sociales">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">OG Description
                                            <small class="text-muted fw-normal">(max 200)</small>
                                        </label>
                                        <textarea class="form-control" name="og_description"
                                                  rows="2" maxlength="200"
                                                  placeholder="Descripción para redes sociales">{{ old('og_description', $seoMeta->og_description) }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">OG Image URL</label>
                                        <input type="url" class="form-control" id="og_image" name="og_image"
                                               value="{{ old('og_image', $seoMeta->og_image) }}"
                                               placeholder="https://ejemplo.com/imagen.jpg">
                                        <div id="og-image-preview" class="mt-2 d-none">
                                            <img src="" alt="Vista previa OG" class="img-fluid rounded border og-image-preview-img">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">OG Type</label>
                                        <select class="form-select" name="og_type">
                                            @foreach(['website' => 'Website', 'article' => 'Article', 'product' => 'Product'] as $val => $label)
                                                <option value="{{ $val }}" @selected(old('og_type', $seoMeta->og_type) === $val)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Twitter --}}
                            <div role="tabpanel" class="tab-pane fade" id="pane-twitter" aria-labelledby="tab-twitter">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Twitter card</label>
                                        <select class="form-select" name="twitter_card">
                                            <option value="summary" @selected(old('twitter_card', $seoMeta->twitter_card) === 'summary')>Summary</option>
                                            <option value="summary_large_image" @selected(old('twitter_card', $seoMeta->twitter_card) === 'summary_large_image')>Summary large image</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Twitter title</label>
                                        <input type="text" class="form-control" name="twitter_title"
                                               value="{{ old('twitter_title', $seoMeta->twitter_title) }}"
                                               placeholder="Título para Twitter">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Twitter description</label>
                                        <textarea class="form-control" name="twitter_description"
                                                  rows="2"
                                                  placeholder="Descripción para Twitter">{{ old('twitter_description', $seoMeta->twitter_description) }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Twitter image URL</label>
                                        <input type="url" class="form-control" name="twitter_image"
                                               value="{{ old('twitter_image', $seoMeta->twitter_image) }}"
                                               placeholder="https://ejemplo.com/twitter-imagen.jpg">
                                    </div>
                                </div>
                            </div>

                            {{-- Técnico --}}
                            <div role="tabpanel" class="tab-pane fade" id="pane-tecnico" aria-labelledby="tab-tecnico">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">URL canónica</label>
                                        <input type="url" class="form-control" name="canonical_url"
                                               value="{{ old('canonical_url', $seoMeta->canonical_url) }}"
                                               placeholder="https://ejemplo.com/pagina">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold">Robots</label>
                                        <select class="form-select" name="robots">
                                            @foreach([
                                                'index,follow'    => 'index,follow',
                                                'noindex,follow'  => 'noindex,follow',
                                                'noindex,nofollow'=> 'noindex,nofollow',
                                                'index,nofollow'  => 'index,nofollow',
                                            ] as $val => $label)
                                                <option value="{{ $val }}" @selected(old('robots', $seoMeta->robots) === $val)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold">Schema type</label>
                                        <input type="text" class="form-control" name="schema_type"
                                               value="{{ old('schema_type', $seoMeta->schema_type) }}"
                                               placeholder="Course, Article, Product...">
                                    </div>
                                </div>
                            </div>

                            {{-- Schema.org custom --}}
                            <div role="tabpanel" class="tab-pane fade" id="pane-schema" aria-labelledby="tab-schema">
                                <label class="form-label fw-semibold">JSON-LD personalizado</label>
                                <textarea class="form-control font-monospace" id="schema_custom" name="schema_custom"
                                          rows="10"
                                          placeholder='{"@@context":"https://schema.org","@@type":"Course","name":"..."}'>{{ old('schema_custom', $seoMeta->schema_custom ? json_encode($seoMeta->schema_custom, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-primary w-100" id="btn-validate-json">
                                        Validar JSON
                                    </button>
                                    <span id="json-validation-msg" class="small d-block mt-2"></span>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- Botones dentro de la MISMA card (no una card aparte
                         con espacio de por medio) -- mismo patrón que
                         "Configuración del sistema" en webadmin, donde el
                         card-footer con los botones cierra directamente el
                         card que contiene las tabs. --}}
                    <div class="card-footer">
                        <button type="button" class="btn btn-primary w-100 mb-2" id="btn-save-meta">
                            Guardar cambios
                        </button>
                        <a href="{{ route('manager.seo.metas.index') }}" class="btn btn-light w-100">
                            Cancelar
                        </a>
                    </div>
                </div>

            </form>
        </div>

        {{-- Columna de ayuda: una sola tarjeta combinada (propuesta A del
             /design) en vez de dos tarjetas separadas -- filas de
             información en línea (label izquierda, valor derecha) en vez de
             list-group apilado, y el checklist con circulos de check en vez
             de icono suelto. --}}
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-body p-4">
                    <h6 class="mb-1 fw-bold">Información del recurso</h6>
                    <p class="text-muted small mb-0">Página vinculada a este meta</p>
                </div>

                <div class="px-4 pb-2">
                    <div class="d-flex justify-content-between align-items-center py-2 border-top">
                        <span class="small text-muted fw-semibold">Tipo</span>
                        <span class="small fw-bold">{{ class_basename($seoMeta->seoable_type) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-top">
                        <span class="small text-muted fw-semibold">ID del recurso</span>
                        <span class="small fw-bold">#{{ $seoMeta->seoable_id }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-top">
                        <span class="small text-muted fw-semibold">Creado</span>
                        <span class="small fw-semibold">{{ $seoMeta->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-top">
                        <span class="small text-muted fw-semibold">Última actualización</span>
                        <span class="small fw-semibold">{{ $seoMeta->updated_at->diffForHumans() }}</span>
                    </div>
                    @if($seoMeta->score)
                        @php
                            $sc = $seoMeta->score;
                            $scClass = match($sc) {
                                'A' => 'bg-success', 'B' => 'bg-info',
                                'C' => 'bg-warning text-dark',
                                default => 'bg-danger'
                            };
                        @endphp
                        <div class="d-flex justify-content-between align-items-center py-2 border-top">
                            <span class="small text-muted fw-semibold">Score SEO</span>
                            <span class="badge {{ $scClass }}">{{ $sc }}</span>
                        </div>
                    @endif
                </div>

                <hr class="my-0">

                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Guía rápida</h6>
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fas fa-circle-check text-success mt-1"></i>
                            <span class="small text-muted">Title: entre 50 y 70 caracteres</span>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                            <i class="fas fa-circle-check text-success mt-1"></i>
                            <span class="small text-muted">Description: entre 120 y 170 caracteres</span>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                            <i class="fas fa-circle-check text-success mt-1"></i>
                            <span class="small text-muted">OG Image: 1200×630 px recomendado</span>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                            <i class="fas fa-circle-check text-success mt-1"></i>
                            <span class="small text-muted">Incluye la keyword principal en el título</span>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                            <i class="fas fa-circle-check text-success mt-1"></i>
                            <span class="small text-muted">JSON-LD debe ser JSON válido</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('css')
{{-- ?v=filemtime: sin esto el navegador puede servir una copia en caché de
     antes del último cambio a este CSS -- pasó justo con el fix del
     border-bottom de #seoMetaTab, invisible hasta forzar recarga. --}}
<link rel="stylesheet" href="{{ asset('managers/css/views/seo/metas/edit.css') }}?v={{ @filemtime(public_path('managers/css/views/seo/metas/edit.css')) ?: 1 }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/seo/metas/edit.js') }}?v={{ @filemtime(public_path('managers/js/views/seo/metas/edit.js')) ?: 1 }}"></script>
@endpush
