@extends('layouts.managers')

@section('title', 'Registro de actividad')

@section('page_header')
    @php ob_start(); @endphp
    <div class="btn-group">
        <button type="button" class="btn btn-icon btn-actions-icon"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Acciones">
            <i class="fas fa-ellipsis-vertical"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item" href="{{ route('manager.activity.export', request()->query()) }}">Exportar CSV</a>
            <div class="dropdown-divider"></div>
            <button id="refresh-stats-btn" type="button" class="dropdown-item">Refrescar</button>
        </div>
    </div>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Registro de actividad',
        'description' => 'Auditoría de cambios: quién modificó qué y cuándo',
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    <div id="activityPage" class="widget-content searchable-container list"
         data-bulk-action-url="{{ route('manager.activity.bulk-action') }}"
         data-stats-url="{{ route('manager.activity.stats') }}">

                <div id="ajax-table-root">
            @include('managers.views.activity._table')
        </div>

    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'registro(s) de auditoría',
        'bulkActions' => [
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    {{-- Modal de detalle --}}
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalle del cambio <small class="text-muted" id="detail-meta"></small></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Campo</th><th>Antes</th><th>Después</th></tr>
                            </thead>
                            <tbody id="detail-body"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros avanzados --}}
    <div class="modal fade" id="activity-filter-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Filtros avanzados</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Evento</label>
                        <select id="modal-event" class="form-control select2-filter-modal">
                            <option value="">Todos los eventos</option>
                            @foreach($events as $ev)
                                <option value="{{ $ev }}" @selected(request('event') === $ev)>{{ ucfirst($ev) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Módulo</label>
                        <select id="modal-log-name" class="form-control select2-filter-modal">
                            <option value="">Todos los módulos</option>
                            @foreach($logNames as $logName)
                                <option value="{{ $logName }}" @selected(request('log_name') === $logName)>{{ ucfirst($logName) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Entidad</label>
                        <select id="modal-subject-type" class="form-control select2-filter-modal">
                            <option value="">Todas las entidades</option>
                            @foreach($subjectTypes as $type)
                                <option value="{{ $type }}" @selected(request('subject_type') === $type)>{{ class_basename($type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Autor</label>
                        <input type="text" id="modal-causer" class="form-control" placeholder="Nombre o email" value="{{ request('causer') }}">
                    </div>
                    <div class="row g-2 mb-0">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Desde</label>
                            <input type="date" id="modal-date-from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Hasta</label>
                            <input type="date" id="modal-date-to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="activity-filter-apply-btn" class="btn btn-primary w-100 mb-2">Aplicar filtros</button>
                    <button type="button" id="activity-filter-clear-btn" class="btn btn-secondary w-100">Limpiar</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/activity/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/activity/index.js') }}"></script>
@endpush
