@extends('layouts.managers')

@section('title', 'Crear variable de email')

@section('page_header')
    @include('managers.includes.card', ['title' => 'Crear variable de email'])
@endsection

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

<div class="row g-4 align-items-start">

    {{-- Columna izquierda: formulario --}}
    <div class="col-lg-8">
        <div class="card">
            <form action="{{ route('mailers.variables.store') }}" method="POST">
                @csrf

                <div class="card-header border-bottom p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-1 fw-bold">Crear nueva variable de email</h5>
                            <p class="text-muted mb-0">Define la clave, categoría y descripción de la variable.</p>
                        </div>
                        <a href="{{ route('mailers.variables.index') }}" class="btn btn-light">
                            Volver
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <h6 class="fw-bold mb-3 border-bottom pb-2">Información básica</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="key" class="form-label">
                                Clave de variable <span class="text-danger">*</span>
                                <p class="text-muted">(solo mayúsculas y guiones bajos)</p>
                            </label>
                            <input type="text" class="form-control @error('key') is-invalid @enderror"
                                   id="key" name="key"
                                   placeholder="Ej: CUSTOMER_NAME, ORDER_NUMBER"
                                   value="{{ old('key') }}" required
                                   pattern="^[A-Z][A-Z0-9_]+$" title="Debe comenzar con mayúscula; solo mayúsculas, números y guiones bajos">
                            @error('key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name"
                                   placeholder="Ej: Nombre del cliente"
                                   value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label for="category" class="form-label">Categoría <span class="text-danger">*</span></label>
                            <select class="form-select select2 @error('category') is-invalid @enderror" id="category" name="category" required>
                                <option value="">Seleccionar categoría</option>
                                @foreach($categories as $value => $label)
                                    <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label for="module" class="form-label">Módulo <span class="text-danger">*</span></label>
                            <select class="form-select select2 @error('module') is-invalid @enderror" id="module" name="module" required>
                                <option value="">Seleccionar módulo</option>
                                @foreach($modules as $value => $label)
                                    <option value="{{ $value }}" @selected(old('module') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('module')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label for="is_enabled" class="form-label">Estado</label>
                            <select class="form-select select2 @error('is_enabled') is-invalid @enderror" id="is_enabled" name="is_enabled">
                                <option value="1" @if(old('is_enabled', '1') == '1') selected @endif>Habilitada</option>
                                <option value="0" @if(old('is_enabled') == '0') selected @endif>Deshabilitada</option>
                            </select>
                            @error('is_enabled')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label">Descripción</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description"
                                      placeholder="Describe qué representa esta variable y cuándo se utiliza"
                                      rows="3">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_system" name="is_system" value="1" @checked(old('is_system'))>
                                <label class="form-check-label" for="is_system">
                                    <strong>Variable del sistema</strong>
                                    <small class="text-muted d-block">Marcar si esta variable es crítica para el sistema</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer border-top">
                    <button type="submit" class="btn btn-primary w-100 mb-1">Crear variable</button>
                    <a href="{{ route('mailers.variables.index') }}" class="btn btn-light w-100">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Columna derecha: sidebar informativo --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header border-bottom">
                <h6 class="mb-0 fw-bold">Sobre las variables</h6>
            </div>
            <div class="card-body">
                <ul class="text-muted ps-3 mb-0">
                    <li class="mb-2">La <strong>clave</strong> se usa entre llaves dentro de las plantillas, ej: <code>{CUSTOMER_NAME}</code></li>
                    <li class="mb-2">Debe comenzar con mayúscula y usar solo mayúsculas, números y guiones bajos</li>
                    <li class="mb-2">La <strong>categoría</strong> y el <strong>módulo</strong> agrupan la variable al listarla</li>
                    <li>Marca <strong>variable del sistema</strong> solo si es crítica: luego no podrá editarse ni eliminarse</li>
                </ul>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/mailer/variables/create.js') }}"></script>
@endpush
