@extends('layouts.managers')

@section('title', 'Historial de auditorías SEO')

@section('page_header')
    @php ob_start(); @endphp
    <div class="btn-group">
        <button type="button" class="btn btn-icon btn-actions-icon dropdown-toggle arrow-none"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Acciones">
            <i class="fas fa-ellipsis-vertical"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            {{-- id="btn-clear-history": public/managers/js/views/seo/audit/history.js
                 ya escuchaba el click de este id para abrir #modal-clear-history,
                 pero ningún botón con ese id existía en la vista -- el modal de
                 confirmación quedaba sin forma de abrirse. --}}
            <button type="button" class="dropdown-item" id="btn-clear-history">
                Limpiar historial
            </button>
        </div>
    </div>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Historial de auditorías SEO',
        'description' => 'Registro de todas las auditorías ejecutadas sobre metas SEO',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div class="widget-content searchable-container list">

        <div id="ajax-table-root">
            @include('managers.views.seo.audit._history', ['logs' => $logs, 'stats' => $stats])
        </div>

    </div>

    <div id="audit-history-config" class="d-none"
         data-bulk-url="{{ route('manager.seo.audit.history.bulk-action') }}"
         data-clear-url="{{ route('manager.seo.audit.history.clear') }}"></div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'auditoría(s)',
        'bulkActions' => [
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    {{-- Modal confirmar limpiar historial --}}
    <div class="modal fade" id="modal-clear-history" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Limpiar historial de auditorías</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="display-4 text-warning mb-3">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h5 class="fw-bold mb-2">¿Limpiar todo el historial?</h5>
                    <p class="text-muted mb-0">
                        Se eliminarán todos los registros de auditorías guardados.
                        Esta accion no se puede deshacer.
                    </p>
                </div>
                <div class="modal-footer flex-column">
                    <button id="btn-confirm-clear" type="button" class="btn btn-primary w-100 mb-2">
                        Confirmar limpieza
                    </button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/seo/audit/history.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/seo/audit/history.js') }}"></script>
@endpush
