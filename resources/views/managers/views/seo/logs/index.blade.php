@extends('layouts.managers')

@section('title', 'Errores 404')

@section('content')


    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Errores 404</h5>
                        <p class="mb-0 text-muted">Registro de URLs no encontradas con mayor frecuencia</p>
                    </div>
                    <div class="ms-auto">
                        <button type="button" class="btn btn-outline-danger" id="btn-clean-old">
                            Limpiar registros (+90 días)
                        </button>
                    </div>
                </div>
            </div>

            {{-- Stats --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Total 404s</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['total'] ?? 0) }}</h4>
                                <span class="text-muted">Errores registrados</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Sin redirect</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['unresolved'] ?? 0) }}</h4>
                                <span class="text-muted">Sin resolver</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Resueltos</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['resolved'] ?? 0) }}</h4>
                                <span class="text-muted">Con redirect</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filtros --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('manager.seo.logs.index') }}" id="searchForm">
                    <div class="row g-2">
                        <div class="col-6 col-md-auto">
                            <select name="has_redirect" class="form-select">
                                <option value="">Todos</option>
                                <option value="0" @selected(request('has_redirect') === '0')>Sin resolver</option>
                                <option value="1" @selected(request('has_redirect') === '1')>Resueltos</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                        </div>
                        @if(request('has_redirect') !== null && request('has_redirect') !== '')
                            <div class="col-auto">
                                <a href="{{ route('manager.seo.logs.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Tabla --}}
            <div class="card-body">
                @if($logs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap">
                            <thead class="table-light">
                                <tr>
                                    <th width="3%">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>URL</th>
                                    <th>Hits</th>
                                    <th>Último acceso</th>
                                    <th>Referer</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox" value="{{ $log->id }}">
                                        </td>
                                        <td class="log-path-col">
                                            <code class="small text-break">{{ Str::limit($log->path, 70) }}</code>
                                        </td>
                                        <td>
                                            <span class="badge {{ $log->hit_count >= 100 ? 'bg-danger' : ($log->hit_count >= 10 ? 'bg-warning text-dark' : 'bg-light text-dark border') }}">
                                                {{ number_format($log->hit_count) }}
                                            </span>
                                        </td>
                                        <td>
                                            <p class="text-muted">{{ $log->last_seen_at?->diffForHumans() ?? '—' }}</p>
                                        </td>
                                        <td class="log-referer-col">
                                            <small class="text-muted text-break">{{ Str::limit($log->referer, 50) ?: '—' }}</small>
                                        </td>
                                        <td>
                                            @if($log->has_redirect)
                                                <span class="badge bg-success">Resuelto</span>
                                            @else
                                                <span class="badge bg-danger">Sin redirect</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0"
                                                        data-bs-toggle="dropdown"
                                                        data-bs-boundary="viewport">
                                                    <i class="fas fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item btn-create-redirect" href="#"
                                                           data-path="{{ $log->path }}"
                                                           data-id="{{ $log->id }}">
                                                            Crear redirect
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item btn-mark-resolved" href="#"
                                                           data-id="{{ $log->id }}"
                                                           data-url="{{ route('manager.seo.logs.mark-resolved', $log->id) }}">
                                                            Marcar resuelto
                                                        </a>
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
                        <i class="fas fa-circle-check fa-3x mb-3 text-success opacity-75"></i>
                        <h5 class="fw-bold mb-2">No hay errores 404 registrados</h5>
                        <p class="text-muted mb-4">El sitio no tiene errores 404 pendientes</p>
                    </div>
                @endif
            </div>

            {{-- Paginación --}}
            @if($logs->hasPages())
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Mostrando {{ $logs->firstItem() }} - {{ $logs->lastItem() }}
                            de {{ $logs->total() }}
                        </div>
                        <div>
                            {{ $logs->appends(request()->input())->links() }}
                        </div>
                    </div>
                </div>
            @endif

        </div>

    </div>

    <div id="logs-config" class="d-none"
         data-bulk-destroy-url="{{ route('manager.seo.logs.bulk-destroy') }}"
         data-create-redirect-url="{{ route('manager.seo.logs.create-redirect') }}"
         data-clear-url="{{ route('manager.seo.logs.clear') }}"></div>

    {{-- Bulk toolbar --}}
    <div id="bulk-toolbar" class="position-fixed bottom-0 start-50 translate-middle-x mb-4 d-none bulk-toolbar-float">
        <button type="button" class="btn btn-primary shadow-lg px-4"
                data-bs-toggle="modal" data-bs-target="#bulk-modal">
            <span data-bulk-count>0</span> seleccionado(s) &mdash; Aplicar accion
        </button>
    </div>

    {{-- Bulk modal --}}
    <div class="modal fade" id="bulk-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Accion masiva</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        Se aplicara la accion sobre <strong><span data-bulk-count>0</span> registro(s)</strong>.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Accion</label>
                        <select id="bulk-action-select" class="form-select">
                            <option value="">Seleccionar accion...</option>
                            <option value="delete">Eliminar</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button id="bulk-apply-btn" type="button" class="btn btn-primary w-100 mb-2">Aplicar</button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal crear redirect desde un 404 --}}
    <div class="modal fade" id="modalCreateRedirect" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crear redirect para este 404</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formCreateRedirect">
                        <input type="hidden" id="log-id" name="log_id">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">URL de origen</label>
                            <input type="text" class="form-control bg-light" id="redirect-source" name="source_path" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">URL de destino</label>
                            <input type="text" class="form-control" id="redirect-target" name="target_path"
                                   placeholder="/nueva-ruta">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold">Código de estado</label>
                            <select class="form-select" id="redirect-code" name="status_code">
                                <option value="301">301 — Permanente</option>
                                <option value="302">302 — Temporal</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer d-block">
                    <button type="button" class="btn btn-primary w-100 mb-2" id="btn-save-log-redirect">
                        Crear redirect
                    </button>
                    <button type="button" class="btn btn-light w-100" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/seo/logs/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/seo/logs/index.js') }}"></script>
@endpush

