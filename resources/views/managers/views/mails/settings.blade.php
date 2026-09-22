@extends('layouts.managers')

@section('page_header')
    @include('managers.includes.card', ['title' => 'Configuración de correos entrantes'])
@endsection

@section('content')


    <div class="row g-3" id="mails-settings"
         data-config='@php $__jsonInline1 = [
            "routes" => [
                "enterprises" => route("manager.mails.enterprises"),
                "store" => route("manager.mails.rules.store"),
                "rulesBase" => url("panel/settings/mails/rules"),
            ],
         ]; @endphp@json($__jsonInline1)'>

        {{-- Reglas de auto-confirmación --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header p-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <h6 class="mb-0 fw-bold">Reglas de auto-confirmación</h6>
                            <p class="text-muted">
                                Cuando un correo de una empresa alcance la confianza mínima y todos los cursos tengan alias registrados,
                                se confirmará automáticamente sin revisión manual.
                            </p>
                        </div>
                        <a href="{{ route('manager.mails.index') }}" class="btn btn-outline-secondary btn-sm">
                            Volver al listado
                        </a>
                    </div>
                </div>

                {{-- Formulario nueva regla --}}
                <div class="card-body border-bottom bg-light">
                    <h6 class="fw-semibold mb-3 small text-muted">AGREGAR NUEVA REGLA</h6>
                    <div class="row g-2 align-items-end" id="new-rule-form">
                        <div class="col-md-5">
                            <label class="form-label form-label-sm mb-1">Empresa</label>
                            <select class="form-select w-100" id="rule-enterprise">
                                <option></option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label form-label-sm mb-1">
                                Confianza mínima: <strong id="confidence-display">90%</strong>
                            </label>
                            <input type="range" class="form-range" id="rule-confidence"
                                   min="50" max="100" step="5" value="90">
                        </div>
                        <div class="col-md-3">
                            <button type="button" id="save-rule-btn" class="btn btn-primary w-100">
                                Agregar regla
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Tabla de reglas --}}
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" id="rules-table">
                            <thead class="header-item">
                                <tr>
                                    <th>Empresa</th>
                                    <th class="text-center mails-settings-col-150">Confianza mínima</th>
                                    <th class="text-center mails-settings-col-120">Estado</th>
                                    <th class="text-center mails-settings-col-100">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="rules-tbody">
                                @forelse($rules as $rule)
                                    <tr id="rule-row-{{ $rule->id }}">
                                        {{-- La empresa puede haberse borrado (soft delete) después de crear la regla --}}
                                        <td class="fw-semibold">{{ $rule->enterprise->title ?? 'Empresa eliminada' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-primary rounded-3 py-1 px-2">≥ {{ $rule->min_confidence }}%</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check form-switch d-inline-block mb-0">
                                                <input class="form-check-input rule-toggle" type="checkbox"
                                                       data-id="{{ $rule->id }}"
                                                       {{ $rule->is_active ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown dropstart">
                                                <a href="#" class="text-muted" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-vertical fs-5"></i>
                                                </a>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item delete-rule-btn" href="#"
                                                           data-id="{{ $rule->id }}">
                                                            Eliminar
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="empty-row">
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            No hay reglas configuradas
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/mails/settings.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/mails/settings.js') }}"></script>
@endpush
