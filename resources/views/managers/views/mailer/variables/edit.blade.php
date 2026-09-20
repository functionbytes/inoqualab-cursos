@extends('layouts.managers')

@section('title', 'Editar variable: ' . $variable->key)

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul><button class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <form action="{{ route('mailers.variables.update', $variable) }}" method="POST">
                @csrf
                @method('PATCH')

                {{-- Hidden fields for system variables --}}
                @if($variable->is_system)
                    <input type="hidden" name="key" value="{{ $variable->key }}">
                    <input type="hidden" name="category" value="{{ $variable->category }}">
                    <input type="hidden" name="module" value="{{ $variable->module }}">
                    <input type="hidden" name="is_system" value="1">
                @endif

                <div class="card-header border-bottom p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-1 fw-bold">Editar: {{ $variable->key }}</h5>
                            <p class="text-muted mb-0">Actualiza la información de la variable.</p>
                        </div>
                        <a href="{{ route('mailers.variables.index') }}" class="btn btn-light">
                            Volver
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="key" class="form-label">
                                Clave de variable <span class="text-danger">*</span>
                                <p class="text-muted">(solo mayúsculas y guiones bajos)</p>
                            </label>
                            <input type="text" class="form-control @error('key') is-invalid @enderror"
                                   id="key" name="key"
                                   value="{{ old('key', $variable->key) }}" required
                                   pattern="^[A-Z][A-Z0-9_]+$" title="Debe comenzar con mayúscula; solo mayúsculas, números y guiones bajos"
                                   @if($variable->is_system) disabled @endif>
                            @error('key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @if($variable->is_system)
                                <small class="form-text text-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i>No se puede modificar la clave de variables del sistema
                                </small>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name"
                                   value="{{ old('name', $variable->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label for="category" class="form-label">Categoría <span class="text-danger">*</span></label>
                            <select class="form-select select2 @error('category') is-invalid @enderror"
                                    id="category" name="category" required
                                    @if($variable->is_system) disabled @endif>
                                <option value="">Seleccionar categoría</option>
                                @foreach($categories as $value => $label)
                                    <option value="{{ $value }}" @selected(old('category', $variable->category) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label for="module" class="form-label">Módulo <span class="text-danger">*</span></label>
                            <select class="form-select select2 @error('module') is-invalid @enderror"
                                    id="module" name="module" required
                                    @if($variable->is_system) disabled @endif>
                                <option value="">Seleccionar módulo</option>
                                @foreach($modules as $value => $label)
                                    <option value="{{ $value }}" @selected(old('module', $variable->module) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('module')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label for="is_enabled" class="form-label">Estado</label>
                            <select class="form-select select2 @error('is_enabled') is-invalid @enderror" id="is_enabled" name="is_enabled">
                                <option value="1" @if(old('is_enabled', $variable->is_enabled) == 1) selected @endif>Habilitada</option>
                                <option value="0" @if(old('is_enabled', $variable->is_enabled) == 0) selected @endif>Deshabilitada</option>
                            </select>
                            @error('is_enabled')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label">Descripción</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description"
                                      placeholder="Describe qué representa esta variable"
                                      rows="3">{{ old('description', $variable->description) }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        @if($variable->is_system)
                            <div class="col-12">
                                <div class="alert alert-info border-0">
                                    <i class="fas fa-shield-alt me-2"></i>
                                    <strong>Variable del sistema:</strong> Esta variable es parte del núcleo del sistema y tiene restricciones de edición.
                                </div>
                            </div>
                        @endif

                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold mb-1">Valor de prueba</h6>
                            <p class="text-muted mb-2 small">Valor visual de ejemplo que se usará en previsualizaciones</p>
                            <textarea class="form-control @error('test_value') is-invalid @enderror"
                                      id="test_value" name="test_value"
                                      placeholder="Valor de ejemplo para esta variable"
                                      rows="2">{{ old('test_value', $variable->test_value ?? '') }}</textarea>
                            @error('test_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="form-text text-muted"><i class="fas fa-info-circle me-1"></i>Dejar vacío para usar el valor predeterminado del sistema</small>
                        </div>
                    </div>
                </div>

                <div class="card-footer border-top">
                    <button type="submit" class="btn btn-primary w-100 mb-1">Guardar</button>
                    <a href="{{ route('mailers.variables.index') }}" class="btn btn-light w-100">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/mailer/variables/edit.js') }}"></script>
@endpush
