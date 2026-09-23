@extends('layouts.managers')

@section('title', 'Editar URL estática')

@section('page_header')
    @include('managers.includes.card', ['title' => 'Editar URL estática'])
@endsection

@section('content')


    <div class="row" data-flash-success="{{ session('success') }}" data-flash-error="{{ session('error') }}">

        {{-- Formulario principal --}}
        <div class="col-lg-8">
            <div class="card">
                <form id="formStaticUrl" action="{{ route('manager.seo.static-urls.update', $staticUrl) }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Editar URL estática</h6>
                        <p class="text-muted small mb-0">Actualiza la configuración de la URL estática.</p>
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
                                           value="{{ old('url', $staticUrl->url) }}"
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
                                        $currentPriority = old('priority', number_format($staticUrl->priority, 1));
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
                                            <option value="{{ $optionValue }}" {{ $currentPriority == $optionValue ? 'selected' : '' }}>{{ $optionValue }} — {{ $priorityLabels[$optionValue] ?? '' }}</option>
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
                                        $currentFreq = old('changefreq', $staticUrl->changefreq);
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
                                            <option value="{{ $option }}" {{ $currentFreq == $option ? 'selected' : '' }}>{{ $option }} — {{ $changefreqLabels[$option] ?? '' }}</option>
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
                                              placeholder="Descripción opcional para identificar esta URL...">{{ old('notes', $staticUrl->notes) }}</textarea>
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
                                    @php $currentActive = old('is_active', $staticUrl->is_active ? '1' : '0'); @endphp
                                    <div class="form-check form-switch mt-1">
                                        <input class="form-check-input" type="checkbox"
                                               id="is_active"
                                               name="is_active"
                                               value="1"
                                               {{ $currentActive == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Activa — se incluira en el sitemap
                                        </label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="card-footer d-flex flex-column gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            Guardar cambios
                        </button>
                        <a href="{{ route('manager.seo.static-urls.index') }}" class="btn btn-light border w-100">
                            Cancelar
                        </a>
                    </div>

                </form>
            </div>
        </div>

        {{-- Panel lateral --}}
        <div class="col-lg-4">

            {{-- Metadatos del registro --}}
            <div class="card mb-3">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Información del registro</h6>
                </div>
                <div class="card-body">
                    <dl class="row small mb-0">
                        <dt class="col-5 text-muted">ID</dt>
                        <dd class="col-7">{{ $staticUrl->id }}</dd>
                        <dt class="col-5 text-muted">Creada</dt>
                        <dd class="col-7">{{ $staticUrl->created_at?->format('d/m/Y H:i') }}</dd>
                        <dt class="col-5 text-muted">Modificada</dt>
                        <dd class="col-7 mb-0">{{ $staticUrl->updated_at?->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>

            {{-- Guia rapida --}}
            <div class="card mb-3">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Prioridad</h6>
                </div>
                <div class="card-body">
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
                        <li class="mb-1"><strong>always</strong> — Cambia en cada visita</li>
                        <li class="mb-1"><strong>hourly</strong> — Cambia cada hora</li>
                        <li class="mb-1"><strong>daily</strong> — Cambia a diario</li>
                        <li class="mb-1"><strong>weekly</strong> — Semanal (recomendado)</li>
                        <li class="mb-1"><strong>monthly</strong> — Mensual</li>
                        <li class="mb-1"><strong>yearly</strong> — Anual</li>
                        <li><strong>never</strong> — Contenido archivado</li>
                    </ul>
                </div>
            </div>

            {{-- Zona de peligro --}}
            <div class="card border-danger">
                <div class="card-header border-bottom border-danger">
                    <h6 class="mb-0 fw-bold">Zona de peligro</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Eliminar esta URL no se puede deshacer y dejara de aparecer en el sitemap.
                    </p>
                    <button type="button"
                            class="btn btn-danger w-100 btn-delete"
                            data-url="{{ route('manager.seo.static-urls.destroy', $staticUrl) }}"
                            data-title="Eliminar: {{ $staticUrl->url }}">
                        Eliminar URL
                    </button>
                </div>
            </div>

        </div>
    </div>

    @include('managers.includes.delete')

@endsection

@push('scripts')
<script src="{{ asset('managers/js/flash-toastr.js') }}"></script>
<script src="{{ asset('managers/js/views/seo/static-urls/edit.js') }}"></script>
@endpush
