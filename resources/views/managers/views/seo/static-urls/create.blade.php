@extends('layouts.managers')

@section('title', 'Nueva URL estática')

@section('page_header')
    @include('managers.includes.card', ['title' => 'Nueva URL estática'])
@endsection

@section('content')


    <div class="row" data-flash-error="{{ session('error') }}">

        {{-- Formulario principal --}}
        <div class="col-lg-8">
            <div class="card">
                <form id="formStaticUrl" action="{{ route('manager.seo.static-urls.store') }}" method="POST" novalidate>
                    @csrf

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Nueva URL estática</h6>
                        <p class="text-muted small mb-0">Agrega una URL estática para incluirla en el sitemap XML.</p>
                    </div>

                    <div class="card-body">
                        <div class="row">

                            {{-- URL --}}
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        URL <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control @error('url') is-invalid @enderror"
                                           id="urlInput"
                                           name="url"
                                           value="{{ old('url') }}"
                                           placeholder="https://tudominio.com/pagina"
                                           maxlength="2048"
                                           required>
                                    @error('url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                        <small class="form-text text-muted">URL completa incluyendo https://</small>
                                    @enderror
                                </div>
                            </div>

                            {{-- Prioridad --}}
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        Prioridad <span class="text-danger">*</span>
                                    </label>
                                    @php
                                        $priorityLabels = [
                                            '1.0' => 'Máxima', '0.9' => 'Muy alta', '0.8' => 'Alta',
                                            '0.7' => 'Media-alta', '0.6' => 'Media', '0.5' => 'Media',
                                            '0.4' => 'Media-baja', '0.3' => 'Baja', '0.2' => 'Muy baja',
                                            '0.1' => 'Mínima',
                                        ];
                                    @endphp
                                    <select class="form-select @error('priority') is-invalid @enderror"
                                            id="priority"
                                            name="priority"
                                            required>
                                        @foreach($priorityOptions as $option)
                                            @php $optionValue = number_format($option, 1); @endphp
                                            <option value="{{ $optionValue }}" {{ old('priority', '0.5') == $optionValue ? 'selected' : '' }}>{{ $optionValue }} — {{ $priorityLabels[$optionValue] ?? '' }}</option>
                                        @endforeach
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                        <small class="form-text text-muted">Importancia relativa (0.0 – 1.0)</small>
                                    @enderror
                                </div>
                            </div>

                            {{-- Frecuencia de cambio --}}
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        Frecuencia de cambio <span class="text-danger">*</span>
                                    </label>
                                    @php
                                        $changefreqLabels = [
                                            'always' => 'Siempre', 'hourly' => 'Por hora', 'daily' => 'Diario',
                                            'weekly' => 'Semanal', 'monthly' => 'Mensual', 'yearly' => 'Anual',
                                            'never' => 'Nunca',
                                        ];
                                    @endphp
                                    <select class="form-select @error('changefreq') is-invalid @enderror"
                                            id="changefreq"
                                            name="changefreq"
                                            required>
                                        @foreach($changefreqOptions as $option)
                                            <option value="{{ $option }}" {{ old('changefreq', 'weekly') == $option ? 'selected' : '' }}>{{ $option }} — {{ $changefreqLabels[$option] ?? '' }}</option>
                                        @endforeach
                                    </select>
                                    @error('changefreq')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                        <small class="form-text text-muted">Con qué frecuencia cambia el contenido</small>
                                    @enderror
                                </div>
                            </div>

                            {{-- Notas --}}
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Notas</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror"
                                              id="notes"
                                              name="notes"
                                              rows="3"
                                              placeholder="Descripción opcional para identificar esta URL...">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                        <small class="form-text text-muted">Campo opcional, solo para referencia interna</small>
                                    @enderror
                                </div>
                            </div>

                            {{-- Estado --}}
                            <div class="col-12">
                                <div class="mb-0">
                                    <label class="form-label fw-semibold">Estado</label>
                                    <div class="form-check form-switch mt-1">
                                        <input class="form-check-input" type="checkbox"
                                               id="is_active"
                                               name="is_active"
                                               value="1"
                                               {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Activa — se incluirá en el sitemap
                                        </label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="card-footer d-flex flex-column gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            Guardar
                        </button>
                        <a href="{{ route('manager.seo.static-urls.index') }}" class="btn btn-light border w-100">
                            Cancelar
                        </a>
                    </div>

                </form>
            </div>
        </div>

        {{-- Panel informativo --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Guía rápida</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-0">
                        Las URLs estáticas son páginas que no se generan dinámicamente pero deben aparecer en el sitemap para que los motores de búsqueda las indexen.
                    </p>
                </div>
                <hr class="my-0">
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-1">Prioridad</h6>
                    <div class="mb-2">
                        <span class="badge bg-primary-subtle text-primary mb-1">1.0 — Muy alta</span>
                        <p class="text-muted small mb-0">Para la pagina principal y secciones clave.</p>
                    </div>
                    <div class="mb-2">
                        <span class="badge bg-primary-subtle text-primary mb-1">0.8 — Alta</span>
                        <p class="text-muted small mb-0">Para secciones importantes como categorias.</p>
                    </div>
                    <div class="mb-2">
                        <span class="badge bg-primary-subtle text-primary mb-1">0.5 — Media</span>
                        <p class="text-muted small mb-0">Valor por defecto, adecuado para la mayoria.</p>
                    </div>
                    <div class="mb-2">
                        <span class="badge bg-primary-subtle text-primary mb-1">0.3 — Baja</span>
                        <p class="text-muted small mb-0">Paginas secundarias con poco trafico.</p>
                    </div>
                    <div>
                        <span class="badge bg-light text-dark border mb-1">0.1 — Muy baja</span>
                        <p class="text-muted small mb-0">Paginas de escasa relevancia SEO.</p>
                    </div>
                </div>
                <hr class="my-0">
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-1">Frecuencia de cambio</h6>
                    <ul class="text-muted small mb-0 ps-3 mt-2">
                        <li class="mb-1"><strong>always</strong> — El contenido cambia en cada visita</li>
                        <li class="mb-1"><strong>hourly</strong> — Cambia cada hora</li>
                        <li class="mb-1"><strong>daily</strong> — Cambia a diario</li>
                        <li class="mb-1"><strong>weekly</strong> — Cambia semanalmente (recomendado)</li>
                        <li class="mb-1"><strong>monthly</strong> — Cambia mensualmente</li>
                        <li class="mb-1"><strong>yearly</strong> — Cambia anualmente</li>
                        <li><strong>never</strong> — Contenido archivado, no cambia</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script src="{{ asset('managers/js/flash-toastr.js') }}"></script>
<script src="{{ asset('managers/js/views/seo/static-urls/create.js') }}"></script>
@endpush

