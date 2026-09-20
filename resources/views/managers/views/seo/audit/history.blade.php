@extends('layouts.managers')

@section('title', 'Historial de auditorías SEO')

@section('content')


    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-1 fw-bold">Historial de auditorías SEO</h5>
                        <p class="mb-0 text-muted">Registro de todas las auditorías ejecutadas sobre metas SEO</p>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <a href="{{ route('manager.seo.audit.index') }}" class="btn btn-outline-secondary">
                            Volver a auditoría
                        </a>
                        <button type="button" class="btn btn-outline-danger" id="btn-clear-history">
                            Limpiar historial
                        </button>
                    </div>
                </div>
            </div>

            {{-- Estadísticas --}}
            <div class="card-body border-bottom">
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

            {{-- Filtros --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('manager.seo.audit.history') }}" id="searchForm">
                    <div class="row g-2">
                        <div class="col-12 col-md">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="search" name="search" class="form-control border-start-0 ps-0"
                                       placeholder="Buscar por URL..."
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-6 col-md-auto">
                            <select name="grade" class="form-select">
                                <option value="">Grade</option>
                                @foreach(['A', 'B', 'C', 'D', 'F'] as $g)
                                    <option value="{{ $g }}" @selected(request('grade') === $g)>{{ $g }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        @if(request('search') || request('grade'))
                            <div class="col-auto">
                                <a href="{{ route('manager.seo.audit.history') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Tabla --}}
            <div class="card-body p-0">
                @if($logs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="3%">
                                        <input type="checkbox" id="select-all" class="form-check-input">
                                    </th>
                                    <th>Meta / URL</th>
                                    <th class="text-center">Score</th>
                                    <th class="text-center">Grade</th>
                                    <th class="text-center">Issues</th>
                                    <th>Fecha auditoría</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                    @php
                                        $grade = $log->grade ?? 'F';
                                        $gradeCls = match($grade) {
                                            'A'     => 'success',
                                            'B'     => 'primary',
                                            'C'     => 'warning',
                                            default => 'danger',
                                        };
                                    @endphp
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox"
                                                   value="{{ $log->id }}">
                                        </td>
                                        <td class="audit-meta-col">
                                            @if($log->seoMeta)
                                                <span class="fw-semibold small d-block text-truncate">
                                                    {{ $log->seoMeta->title ?: ('Meta #' . $log->seoMeta->id) }}
                                                </span>
                                                <code class="text-muted small">
                                                    {{ Str::limit($log->url ?? $log->seoMeta->canonical_url ?? '—', 60) }}
                                                </code>
                                            @else
                                                <code class="small text-muted">{{ Str::limit($log->url ?? '—', 60) }}</code>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <strong>{{ $log->score ?? '—' }}</strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $gradeCls }}">{{ $grade }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ ($log->issues_count ?? 0) > 0 ? 'bg-danger' : 'bg-light text-dark border' }}">
                                                {{ $log->issues_count ?? 0 }}
                                            </span>
                                        </td>
                                        <td>
                                            <p class="text-muted">
                                                {{ $log->created_at->format('d/m/Y H:i') }}
                                            </p>
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0"
                                                        data-bs-toggle="dropdown"
                                                        data-bs-boundary="viewport">
                                                    <i class="fas fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    @if($log->seoMeta)
                                                        <li>
                                                            <a class="dropdown-item"
                                                               href="{{ route('manager.seo.audit.history.meta', $log->seoMeta->id) }}">
                                                                Ver meta SEO
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                    @endif
                                                    <li>
                                                        <button type="button" class="dropdown-item btn-delete-log"
                                                                data-id="{{ $log->id }}"
                                                                data-url="{{ route('manager.seo.audit.history.destroy', $log->id) }}"
                                                                data-title="Eliminar auditoría del {{ $log->created_at->format('d/m/Y H:i') }}">
                                                            Eliminar
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-history fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">
                            @if(request('search') || request('grade'))
                                No se encontraron resultados
                            @else
                                Sin historial de auditorías
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if(request('search') || request('grade'))
                                Intenta con otros filtros de búsqueda
                            @else
                                Ejecuta una auditoría para que aparezca el historial
                            @endif
                        </p>
                        @if(request('search') || request('grade'))
                            <a href="{{ route('manager.seo.audit.history') }}" class="btn btn-outline-secondary">
                                Limpiar filtros
                            </a>
                        @else
                            <a href="{{ route('manager.seo.audit.index') }}" class="btn btn-primary">
                                Ir a auditoría
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Paginación --}}
            @if($logs->hasPages())
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="text-muted small">
                            Mostrando {{ $logs->firstItem() }}–{{ $logs->lastItem() }}
                            de {{ $logs->total() }} registros
                        </div>
                        <div>
                            {{ $logs->appends(request()->input())->links() }}
                        </div>
                    </div>
                </div>
            @endif

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
