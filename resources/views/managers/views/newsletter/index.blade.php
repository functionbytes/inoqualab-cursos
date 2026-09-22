@extends('layouts.managers')

@section('title', 'Newsletter')

@section('page_header')
    @php ob_start(); @endphp
    <div class="btn-group">
        <button type="button" class="btn btn-outline-secondary dropdown-toggle"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Acciones
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item" href="{{ route('manager.newsletter.campaigns.index') }}">
                Campañas
            </a>
            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#add-modal">
                Añadir suscriptor
            </button>
            <div class="dropdown-divider"></div>
            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#import-modal">
                Importar CSV
            </button>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="{{ route('manager.newsletter.export') }}">Exportar CSV</a>
            @if($search || ($status !== null && $status !== '') || ($source !== null && $source !== ''))
                <a class="dropdown-item" href="{{ route('manager.newsletter.export', array_filter(['search' => $search, 'source' => $source, 'status' => $status])) }}">Exportar CSV (filtrado)</a>
            @endif
        </div>
    </div>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Suscriptores del newsletter',
        'description' => 'Gestiona los suscriptores y exporta la lista',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div class="widget-content searchable-container list" id="newsletter-page"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-bulk-url="{{ route('manager.newsletter.bulk-action') }}"
         data-store-url="{{ route('manager.newsletter.store') }}"
         data-import-url="{{ route('manager.newsletter.import') }}">

                <div id="ajax-table-root">
            @include('managers.views.newsletter._table')
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'suscriptor(es)',
        'bulkActions' => [
            ['value' => 'resubscribe', 'label' => 'Reactivar'],
            ['value' => 'unsubscribe', 'label' => 'Desuscribir'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    @include('managers.includes.delete')

    {{-- Modal: añadir suscriptor --}}
    <div class="modal fade" id="add-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Añadir suscriptor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add-email" class="form-label fw-semibold">Correo electrónico <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="add-email" placeholder="correo@ejemplo.com">
                        <div id="add-email-error" class="invalid-feedback"></div>
                    </div>
                    <div class="mb-0">
                        <label for="add-name" class="form-label fw-semibold">Nombre <span class="text-muted fw-normal">(opcional)</span></label>
                        <input type="text" class="form-control" id="add-name" placeholder="Nombre del suscriptor">
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button id="btn-add-confirm" type="button" class="btn btn-primary w-100 mb-2">Añadir</button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: importar CSV --}}
    <div class="modal fade" id="import-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Importar suscriptores desde CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info border-0 py-2 mb-3">
                        <small>
                            El archivo debe tener una columna de <strong>email</strong> y opcionalmente una de <strong>nombre</strong>.
                            La primera fila puede ser una cabecera — se detecta automáticamente.
                        </small>
                    </div>
                    <div class="mb-0">
                        <label for="import-file" class="form-label fw-semibold">Archivo CSV <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="import-file" accept=".csv,.txt">
                        <small class="text-muted d-block mt-1">Máximo 2 MB. Separador: coma o punto y coma.</small>
                        <div id="import-file-error" class="invalid-feedback d-block"></div>
                    </div>
                    <div id="import-result" class="mt-3 d-none">
                        <div class="card bg-light-secondary border-0">
                            <div class="card-body py-2">
                                <p class="mb-0 small" id="import-result-text"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button id="btn-import-confirm" type="button" class="btn btn-primary w-100 mb-2">Importar</button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: filtros avanzados --}}
    

    {{-- Modal desuscribir / reactivar individual --}}
    <div class="modal fade" id="action-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4 position-relative">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    <div class="mb-3 mt-2">
                        <i class="fas fa-triangle-exclamation text-warning action-modal-icon"></i>
                    </div>
                    <h5 class="fw-bold mb-2" id="action-modal-title"></h5>
                    <p class="text-muted mb-4" id="action-modal-body"></p>
                    <button id="btn-action-confirm" type="button" class="btn btn-primary w-100 mb-2">Confirmar</button>
                    <button type="button" class="btn btn-dark w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/newsletter/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/newsletter/index.js') }}"></script>
@endpush
