@extends('layouts.managers')

@section('title', 'Historial de auditorías SEO')

@section('page_header')
    @include('managers.includes.card', [
        'title' => 'Historial de auditorías SEO',
        'description' => 'Registro de todas las auditorías ejecutadas sobre metas SEO',
    ])
@endsection

@section('content')


    <div class="widget-content searchable-container list">

        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body text-center py-3">
                                <h6 class="card-title mb-1 text-muted small">Total auditorías</h6>
                                <h4 class="mb-0 fw-bold">{{ number_format($stats['total_audits'] ?? 0) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body text-center py-3">
                                <h6 class="card-title mb-1 text-muted small">Score promedio</h6>
                                <h4 class="mb-0 fw-bold">
                                    {{ isset($stats['avg_score']) && $stats['avg_score'] > 0 ? $stats['avg_score'] : '—' }}
                                </h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body text-center py-3">
                                <h6 class="card-title mb-1 text-muted small">Grade A</h6>
                                <h4 class="mb-0 fw-bold text-success">{{ number_format($stats['grade_a'] ?? 0) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body text-center py-3">
                                <h6 class="card-title mb-1 text-muted small">Grade F</h6>
                                <h4 class="mb-0 fw-bold text-danger">{{ number_format($stats['grade_f'] ?? 0) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="ajax-table-root">
            @include('managers.views.seo.audit._history', ['logs' => $logs])
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

    {{-- Delete modal individual --}}
    <div id="delete-modal" class="modal fade">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="delete-form" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar eliminacion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <div class="display-4 text-warning mb-3">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h5 class="fw-bold mb-2" id="delete-modal-title">¿Estas seguro?</h5>
                        <p class="text-muted">Esta accion no se puede deshacer.</p>
                    </div>
                    <div class="modal-footer flex-column">
                        <button type="submit" class="btn btn-primary w-100 mb-2">Confirmar eliminacion</button>
                        <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
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
