@extends('layouts.managers')

@section('title', $list ? 'Editar lista' : 'Nueva lista')

@section('page_header')
    @include('managers.includes.card', [
        'title' => $list ? 'Editar lista' : 'Nueva lista',
        'description' => ($list && $list->trigger !== 'manual')
            ? 'Lista dinámica del sistema: se puebla sola por eventos. Solo puedes editar nombre, descripción y estado.'
            : 'Segmento manual de suscriptores para tus campañas.',
    ])
@endsection

@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">{{ $list ? 'Editar lista' : 'Nueva lista' }}</h6>
                </div>
                <div class="card-body">
                    <form id="formList"
                          data-is-new="{{ $list ? 'false' : 'true' }}"
                          data-save-url="{{ $list ? route('manager.newsletter.lists.update', $list->id) : route('manager.newsletter.lists.store') }}"
                          data-save-method="{{ $list ? 'PUT' : 'POST' }}">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="fieldName"
                                   value="{{ old('name', $list?->name) }}" maxlength="255"
                                   placeholder="Ej: Clientes VIP">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Descripción</label>
                            <textarea class="form-control" id="fieldDescription" rows="3" maxlength="500"
                                      placeholder="¿Para qué usas esta lista? (opcional)">{{ old('description', $list?->description) }}</textarea>
                        </div>
                        <div class="mb-0 form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="fieldActive"
                                   {{ old('is_active', $list?->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="fieldActive">Lista activa</label>
                        </div>
                    </form>
                </div>

                <div class="card-footer bg-white border-top d-flex justify-content-end gap-2">
                    <a href="{{ route('manager.newsletter.lists.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="button" id="btnSave" class="btn btn-primary">
                        {{ $list ? 'Guardar cambios' : 'Crear lista' }}
                    </button>
                </div>
            </div>
        </div>

        {{-- Columna derecha: sidebar informativo --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Sobre las listas</h6>
                </div>
                <div class="card-body">
                    @if($list && $list->trigger !== 'manual')
                        <p class="text-muted small mb-0">Esta es una lista dinámica del sistema: se puebla sola cuando ocurre el evento que la origina. Solo puedes editar su nombre, descripción y estado.</p>
                    @else
                        <ul class="text-muted ps-3 mb-0">
                            <li class="mb-2">Las listas manuales se usan para segmentar suscriptores y enviarles campañas específicas</li>
                            <li>Una lista <strong>inactiva</strong> no aparecerá como destino al crear campañas</li>
                        </ul>
                    @endif
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/newsletter/lists/form.js') }}"></script>
@endpush
