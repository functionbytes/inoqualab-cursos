@extends('layouts.managers')

@section('title', $list ? 'Editar lista' : 'Nueva lista')

@section('content')

    <div class="row justify-content-center">
        <div class="col-12 col-lg-7">
            <div class="card">
                <div class="card-header p-4 border-bottom border-light">
                    <h5 class="mb-1 fw-bold">{{ $list ? 'Editar lista' : 'Nueva lista' }}</h5>
                    <p class="small mb-0 text-muted">
                        @if($list && $list->trigger !== 'manual')
                            Lista dinámica del sistema: se puebla sola por eventos. Solo puedes editar nombre, descripción y estado.
                        @else
                            Segmento manual de suscriptores para tus campañas.
                        @endif
                    </p>
                </div>

                <div class="card-body p-4">
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
    </div>

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/newsletter/lists/form.js') }}"></script>
@endpush
