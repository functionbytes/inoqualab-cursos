@extends('layouts.managers')

@section('title', 'Meta SEO')

@section('page_header')
    @php ob_start(); @endphp
<div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary dropdown-toggle"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Acciones
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="{{ route('manager.seo.metas.export') }}">Exportar CSV</a>
                                <a class="dropdown-item" href="{{ route('manager.seo.metas.import') }}">Importar CSV</a>
                                <a class="dropdown-item" href="{{ route('manager.seo.metas.export-json') }}">Exportar JSON</a>
                                <a class="dropdown-item" href="{{ route('manager.seo.metas.import-json') }}">Importar JSON</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('manager.seo.audit.index') }}">Auditoría SEO</a>
                                <button class="dropdown-item" type="button" data-action="reload">Actualizar</button>
                            </div>
                        </div>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Panel de control SEO',
        'description' => 'Auditoría centralizada de configuraciones SEO de todos los modelos del sistema',
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list">

                <div id="ajax-table-root">
            @include('managers.views.seo.metas._table')
        </div>
    </div>

    @include('managers.includes.delete')

    <div id="metas-config" class="d-none"
         data-bulk-destroy-url="{{ route('manager.seo.metas.bulk-destroy') }}"
         data-inline-base-url="{{ url('panel/seo/metas') }}"></div>

    {{-- Bulk toolbar --}}
    <div id="bulk-toolbar" class="position-fixed bottom-0 start-50 translate-middle-x mb-4 d-none bulk-toolbar-float">
        <div class="card shadow-lg border-0">
            <div class="card-body py-2 px-4 d-flex align-items-center gap-3">
                <span class="text-muted small"><span data-bulk-count>0</span> seleccionados</span>
                <button type="button" class="btn btn-danger btn-sm" id="bulk-delete-btn">Eliminar</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="bulk-cancel">Cancelar</button>
            </div>
        </div>
    </div>

    {{-- Modal eliminación masiva --}}
    <div id="bulk-modal" class="modal fade">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center px-4 pb-2">
                    <div class="display-4 text-warning mb-3"><i class="fas fa-exclamation-triangle"></i></div>
                    <h4 class="my-0">¿Eliminar registros seleccionados?</h4>
                    <p class="text-muted mt-2">Se eliminarán <strong id="bulk-count">0</strong> registros meta SEO. Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer flex-column gap-1 border-0 pt-0">
                    <button type="button" id="bulk-delete-confirm" class="btn btn-danger w-100 mb-1">Confirmar eliminación</button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/includes/seo-badges.css') }}">
<link rel="stylesheet" href="{{ asset('managers/css/views/seo/metas/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/seo/metas/index.js') }}"></script>
@endpush
