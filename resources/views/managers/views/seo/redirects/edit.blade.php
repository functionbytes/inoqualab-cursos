@extends('layouts.managers')

@section('title', 'Editar redirección')

@section('content')

    <div class="row">

        {{-- FORMULARIO PRINCIPAL --}}
        <div class="col-lg-8">
            <div class="card">
                <form id="formRedirect" action="{{ route('manager.seo.redirects.update', $seoRedirect) }}" method="POST" novalidate
                      data-flash-success="{{ session('success') }}" data-flash-success-title="Éxito"
                      data-flash-error="{{ session('error') }}" data-flash-error-title="Error">
                    @csrf
                    @method('PUT')

                    <div class="card-header p-3 bg-white border-bottom">
                        <h5 class="mb-0 fw-bold">Editar redirección</h5>
                    </div>

                    <div class="card-body">
                        <div class="row">

                            <div class="col-12 mb-3">
                                <label class="form-label fw-semibold">
                                    Ruta origen <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control @error('source_path') is-invalid @enderror"
                                       id="sourcePath"
                                       name="source_path"
                                       value="{{ old('source_path', $seoRedirect->source_path) }}"
                                       required
                                       placeholder="ej: /pagina-antigua"
                                       maxlength="500">
                                @error('source_path')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <small class="form-text text-muted">La ruta desde la que se redirige</small>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label fw-semibold">
                                    Ruta destino <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control @error('target_path') is-invalid @enderror"
                                       id="targetPath"
                                       name="target_path"
                                       value="{{ old('target_path', $seoRedirect->target_path) }}"
                                       required
                                       placeholder="ej: /pagina-nueva o https://ejemplo.com/pagina"
                                       maxlength="500">
                                @error('target_path')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <small class="form-text text-muted">La ruta o URL completa de destino</small>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    Tipo de redirección <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('status_code') is-invalid @enderror"
                                        id="statusCode"
                                        name="status_code"
                                        required>
                                    <option value="301" {{ old('status_code', $seoRedirect->status_code) == '301' ? 'selected' : '' }}>301 — Permanente</option>
                                    <option value="302" {{ old('status_code', $seoRedirect->status_code) == '302' ? 'selected' : '' }}>302 — Temporal</option>
                                    <option value="307" {{ old('status_code', $seoRedirect->status_code) == '307' ? 'selected' : '' }}>307 — Temporal (preserva método)</option>
                                    <option value="308" {{ old('status_code', $seoRedirect->status_code) == '308' ? 'selected' : '' }}>308 — Permanente (preserva método)</option>
                                </select>
                                @error('status_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <small class="form-text text-muted">301/308 definitivos, 302/307 temporales</small>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                                <select class="form-select @error('is_active') is-invalid @enderror"
                                        id="isActive"
                                        name="is_active"
                                        required>
                                    <option value="1" {{ old('is_active', $seoRedirect->is_active) == 1 ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ old('is_active', $seoRedirect->is_active) == 0 ? 'selected' : '' }}>Inactivo</option>
                                </select>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <small class="form-text text-muted">Solo las activas se aplican</small>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_regex" id="is_regex" value="1"
                                           {{ old('is_regex', $seoRedirect->is_regex) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_regex">
                                        <i class="fas fa-code"></i> Patrón regex
                                        <small class="text-muted ms-1">— expresiones regulares en la ruta de origen</small>
                                    </label>
                                </div>
                            </div>

                            <div class="col-12 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_wildcard" id="is_wildcard" value="1"
                                           {{ old('is_wildcard', $seoRedirect->is_wildcard) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_wildcard">
                                        <i class="fas fa-asterisk"></i> Patrón wildcard
                                        <small class="text-muted ms-1">— usa * para coincidir con cualquier texto</small>
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Activo desde <p class="text-muted">(opcional)</p></label>
                                <input type="datetime-local" name="active_from"
                                       class="form-control @error('active_from') is-invalid @enderror"
                                       value="{{ old('active_from', $seoRedirect->active_from?->format('Y-m-d\TH:i')) }}">
                                @error('active_from')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Activo hasta <p class="text-muted">(opcional)</p></label>
                                <input type="datetime-local" name="active_until"
                                       class="form-control @error('active_until') is-invalid @enderror"
                                       value="{{ old('active_until', $seoRedirect->active_until?->format('Y-m-d\TH:i')) }}">
                                @error('active_until')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <p class="text-muted">Si se establece, expirará automáticamente</p>
                                @enderror
                            </div>

                        </div>
                    </div>

                    <div class="card-footer d-flex flex-column gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            Guardar cambios
                        </button>
                        <a href="{{ route('manager.seo.redirects.index') }}" class="btn btn-light border w-100">
                            Volver
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- PANEL LATERAL --}}
        <div class="col-lg-4">

            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="card-title mb-3">
                        <i class="fas fa-chart-line text-primary me-2"></i>Estadísticas de uso
                    </h6>

                    <div class="text-center mb-3 pb-3 border-bottom">
                        <h3 class="mb-0 fw-bold text-primary">{{ number_format($seoRedirect->hits_count) }}</h3>
                        <p class="text-muted">Total de visitas</p>
                    </div>

                    <div class="row text-center mb-3">
                        <div class="col-6 border-end">
                            <span class="badge {{ $seoRedirect->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} mb-2">
                                {{ $seoRedirect->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                            <br><p class="text-muted">Estado</p>
                        </div>
                        <div class="col-6">
                            <span class="badge bg-primary-subtle text-primary mb-2">
                                {{ $seoRedirect->status_code }}
                            </span>
                            <br><p class="text-muted">Código</p>
                        </div>
                    </div>

                    <div class="small">
                        <div class="mb-2">
                            <span class="text-muted">Creado:</span><br>
                            <strong>{{ $seoRedirect->created_at->format('d/m/Y H:i') }}</strong>
                            <br><p class="text-muted">{{ $seoRedirect->created_at->diffForHumans() }}</p>
                        </div>
                        <div>
                            <span class="text-muted">Última actualización:</span><br>
                            <strong>{{ $seoRedirect->updated_at->format('d/m/Y H:i') }}</strong>
                            <br><p class="text-muted">{{ $seoRedirect->updated_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="card-title mb-3">
                        <i class="fas fa-info-circle text-info me-2"></i>Tipos de redirección
                    </h6>

                    <div class="mb-3">
                        <span class="badge bg-success-subtle text-success mb-2">301 — Permanente</span>
                        <p class="text-muted mb-0">Transfiere el 90-99% del valor SEO.</p>
                    </div>
                    <div class="mb-3">
                        <span class="badge bg-info-subtle text-info mb-2">302 — Temporal</span>
                        <p class="text-muted mb-0">No transfiere valor SEO.</p>
                    </div>
                    <div class="mb-3">
                        <span class="badge bg-warning-subtle text-warning mb-2">307 — Temporal (preserva método)</span>
                        <p class="text-muted mb-0">Similar a 302 pero mantiene el método HTTP.</p>
                    </div>
                    <div class="mb-0">
                        <span class="badge bg-primary-subtle text-primary mb-2">308 — Permanente (preserva método)</span>
                        <p class="text-muted mb-0">Similar a 301 pero mantiene el método HTTP.</p>
                    </div>
                </div>
            </div>

            <div class="card border-danger">
                <div class="card-body bg-danger-subtle">
                    <h6 class="mb-2 text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Zona de peligro
                    </h6>
                    <p class="text-muted mb-3">
                        Eliminar esta redirección no se puede deshacer. Se perderán todas las estadísticas.
                    </p>
                    <button type="button" class="btn btn-danger w-100 delete-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#delete-modal"
                            data-url="{{ route('manager.seo.redirects.destroy', $seoRedirect) }}"
                            data-title="Eliminar: {{ $seoRedirect->source_path }}">
                        Eliminar redirección
                    </button>
                </div>
            </div>

        </div>
    </div>

    @include('managers.includes.delete')
@endsection

@push('scripts')
<script src="{{ asset('managers/js/flash-toastr.js') }}"></script>
<script src="{{ asset('managers/js/views/seo/redirects/edit.js') }}"></script>
@endpush
