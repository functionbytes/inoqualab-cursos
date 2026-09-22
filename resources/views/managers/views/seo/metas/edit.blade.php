@extends('layouts.managers')

@section('title', 'Editar SEO')

@section('page_header')
    @include('managers.includes.card', ['title' => 'Editar SEO'])
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
            <li class="breadcrumb-item active">Editar</li>
        </ol>
    </nav>

    <div class="row g-3">

        {{-- Columna principal --}}
        <div class="col-12 col-lg-8">
            <form id="formSeoMeta" data-update-url="{{ route('manager.seo.metas.update', $seoMeta->id) }}">

                {{-- Acordeón de secciones --}}
                <div class="accordion" id="seoAccordion">

                    {{-- Básico --}}
                    <div class="accordion-item border rounded mb-2">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-semibold" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseBasico">
                                Básico
                            </button>
                        </h2>
                        <div id="collapseBasico" class="accordion-collapse collapse show" data-bs-parent="#seoAccordion">
                            <div class="accordion-body">
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
                        </div>
                    </div>

                    {{-- Open Graph --}}
                    <div class="accordion-item border rounded mb-2">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseOG">
                                Open Graph
                            </button>
                        </h2>
                        <div id="collapseOG" class="accordion-collapse collapse" data-bs-parent="#seoAccordion">
                            <div class="accordion-body">
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
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold">OG Type</label>
                                        <select class="form-select" name="og_type">
                                            @foreach(['website' => 'Website', 'article' => 'Article', 'product' => 'Product'] as $val => $label)
                                                <option value="{{ $val }}" @selected(old('og_type', $seoMeta->og_type) === $val)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Twitter --}}
                    <div class="accordion-item border rounded mb-2">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseTwitter">
                                Twitter / X
                            </button>
                        </h2>
                        <div id="collapseTwitter" class="accordion-collapse collapse" data-bs-parent="#seoAccordion">
                            <div class="accordion-body">
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
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
                        </div>
                    </div>

                    {{-- Técnico --}}
                    <div class="accordion-item border rounded mb-2">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseTecnico">
                                Técnico
                            </button>
                        </h2>
                        <div id="collapseTecnico" class="accordion-collapse collapse" data-bs-parent="#seoAccordion">
                            <div class="accordion-body">
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
                        </div>
                    </div>

                    {{-- Schema.org custom --}}
                    <div class="accordion-item border rounded mb-2">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseSchema">
                                Schema.org personalizado
                            </button>
                        </h2>
                        <div id="collapseSchema" class="accordion-collapse collapse" data-bs-parent="#seoAccordion">
                            <div class="accordion-body">
                                <label class="form-label fw-semibold">JSON-LD personalizado</label>
                                <textarea class="form-control font-monospace" id="schema_custom" name="schema_custom"
                                          rows="10"
                                          placeholder='{"@@context":"https://schema.org","@@type":"Course","name":"..."}'>{{ old('schema_custom', $seoMeta->schema_custom ? json_encode($seoMeta->schema_custom, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                                <div class="mt-2 d-flex gap-2 align-items-center">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-validate-json">
                                        Validar JSON
                                    </button>
                                    <span id="json-validation-msg" class="small"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Botones --}}
                <div class="card mt-3">
                    <div class="card-footer bg-white">
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

        {{-- Columna de ayuda --}}
        <div class="col-12 col-lg-4">
            <div class="card mb-3">
                <div class="card-header border-bottom p-3">
                    <h6 class="mb-0 fw-bold">Información del recurso</h6>
                    <p class="text-muted">Página vinculada a este meta</p>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-3 py-2">
                            <small class="text-muted d-block">Tipo</small>
                            <span class="small fw-semibold">{{ class_basename($seoMeta->seoable_type) }}</span>
                        </div>
                        <div class="list-group-item px-3 py-2">
                            <small class="text-muted d-block">ID del recurso</small>
                            <span class="small fw-semibold">#{{ $seoMeta->seoable_id }}</span>
                        </div>
                        <div class="list-group-item px-3 py-2">
                            <small class="text-muted d-block">Creado</small>
                            <span class="small">{{ $seoMeta->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="list-group-item px-3 py-2">
                            <small class="text-muted d-block">Última actualización</small>
                            <span class="small">{{ $seoMeta->updated_at->diffForHumans() }}</span>
                        </div>
                        @if($seoMeta->score)
                            <div class="list-group-item px-3 py-2">
                                <small class="text-muted d-block">Score SEO</small>
                                @php
                                    $sc = $seoMeta->score;
                                    $scClass = match($sc) {
                                        'A' => 'bg-success', 'B' => 'bg-info',
                                        'C' => 'bg-warning text-dark',
                                        default => 'bg-danger'
                                    };
                                @endphp
                                <span class="badge {{ $scClass }}">{{ $sc }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header border-bottom p-3">
                    <h6 class="mb-0 fw-bold">Guía rápida</h6>
                </div>
                <div class="card-body p-3">
                    <ul class="list-unstyled mb-0 small text-muted">
                        <li class="mb-2">
                            <i class="fas fa-circle-check text-success me-1"></i>
                            Title: entre 50 y 70 caracteres
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-circle-check text-success me-1"></i>
                            Description: entre 120 y 170 caracteres
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-circle-check text-success me-1"></i>
                            OG Image: 1200×630 px recomendado
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-circle-check text-success me-1"></i>
                            Incluye la keyword principal en el título
                        </li>
                        <li>
                            <i class="fas fa-circle-check text-success me-1"></i>
                            JSON-LD debe ser JSON válido
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/seo/metas/edit.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/seo/metas/edit.js') }}"></script>
@endpush
