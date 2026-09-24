@extends('layouts.managers')

@section('title', 'Reglas de correos entrantes')

@section('page_header')
    @php ob_start(); @endphp
<button type="button" class="btn btn-primary btn-icon" data-bs-toggle="modal" data-bs-target="#rule-modal" title="Nueva regla" aria-label="Nueva regla">{!! \App\Html\IconHelper::render('plus') !!}</button>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Reglas de correos entrantes',
        'description' => 'Auto-confirmación de correos por empresa según la confianza de lectura',
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    <div id="rulesPage" class="widget-content searchable-container list"
         data-bulk-action-url="{{ route('manager.mails.rules.bulk-action') }}"
         data-enterprises-url="{{ route('manager.mails.enterprises') }}"
         data-store-url="{{ route('manager.mails.rules.store') }}">

        <div id="ajax-table-root">
            @include('managers.views.mails.settings._table')
        </div>

    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'regla(s)',
        'bulkActions' => [
            ['value' => 'activate', 'label' => 'Activar'],
            ['value' => 'deactivate', 'label' => 'Desactivar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    {{-- Modal nueva regla --}}
    <div id="rule-modal" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva regla</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        Cuando un correo de la empresa alcance la confianza mínima y todos los cursos tengan alias
                        registrados, se confirmará automáticamente sin revisión manual.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="rule-enterprise">Empresa <span class="text-danger">*</span></label>
                        <select class="form-select w-100" id="rule-enterprise">
                            <option></option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold" for="rule-confidence">
                            Confianza mínima: <span id="confidence-display">90%</span>
                        </label>
                        <input type="range" class="form-range" id="rule-confidence" min="50" max="100" step="5" value="90">
                        <small class="text-muted d-block mt-1">Entre más alta, menos correos se confirman solos.</small>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button type="button" id="save-rule-btn" class="btn btn-primary w-100 mb-2">Agregar regla</button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal eliminar --}}
    <div id="delete-modal" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="display-4 text-warning mb-3">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h5 class="fw-bold mb-2" id="delete-modal-title">¿Estás seguro?</h5>
                    <p class="text-muted">Esta acción no se puede deshacer.</p>
                    <div class="d-grid gap-2">
                        <button type="button" id="confirm-delete-btn" class="btn btn-primary" data-url="">Confirmar eliminación</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/mails/settings/index.js') }}?v={{ @filemtime(public_path('managers/js/views/mails/settings/index.js')) ?: '1' }}"></script>
@endpush
