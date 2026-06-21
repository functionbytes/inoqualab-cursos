@extends('layouts.managers')

@section('title', 'Logs de endpoint: ' . $endpoint->name)

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- Endpoint info --}}
<div class="card card-body mb-3">
    <div class="row g-3 align-items-center">
        <div class="col-md-4">
            <h6 class="text-muted mb-1">Endpoint</h6>
            <h5 class="mb-0 fw-bold">{{ $endpoint->name }}</h5>
            <p class="text-muted">{{ $endpoint->slug }}</p>
        </div>
        <div class="col-md-4">
            <h6 class="text-muted mb-1">Clasificación</h6>
            <span class="badge bg-light text-primary rounded-pill py-1 px-2 me-1">{{ $endpoint->source }}</span>
            <span class="badge bg-light text-dark rounded-pill py-1 px-2">{{ $endpoint->type }}</span>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('mailers.endpoints.edit', $endpoint) }}" class="btn btn-sm btn-outline-primary me-1">
                Editar
            </a>
            <a href="{{ route('mailers.endpoints.index') }}" class="btn btn-sm btn-light">
                Atrás
            </a>
        </div>
    </div>
</div>

<div class="card">
    {{-- Stats --}}
    <div class="card-body border-bottom">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="card bg-light h-100">
                    <div class="card-body">
                        <h6 class="card-title text-primary mb-2">Total logs</h6>
                        <h4 class="mb-1 fw-bold">{{ $stats['total'] }}</h4>
                        <p class="text-muted">Registrados</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Exitosos</h6>
                        <h4 class="mb-1 fw-bold">{{ $stats['success'] }}</h4>
                        <p class="text-muted">Enviados</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Fallidos</h6>
                        <h4 class="mb-1 fw-bold">{{ $stats['failed'] }}</h4>
                        <p class="text-muted">Con errores</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light h-100">
                    <div class="card-body">
                        <h6 class="card-title text-info mb-2">Tasa de éxito</h6>
                        <h4 class="mb-1 fw-bold">{{ $stats['success_rate'] }}%</h4>
                        <p class="text-muted">Rendimiento</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card-body border-bottom">
        <form method="GET" action="{{ route('mailers.endpoints.logs', $endpoint) }}">
            <div class="row g-2">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                        <input type="search" name="email" class="form-control"
                               placeholder="Buscar email..."
                               value="{{ $searchEmail ?? '' }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select select2" name="status">
                        <option value="">Todos los estados</option>
                        <option value="pending" @if(($filterStatus ?? '') === 'pending') selected @endif>Pendiente</option>
                        <option value="success" @if(($filterStatus ?? '') === 'success') selected @endif>Éxito</option>
                        <option value="failed" @if(($filterStatus ?? '') === 'failed') selected @endif>Fallido</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select select2" name="period">
                        <option value="">Todos los períodos</option>
                        <option value="24h" @if(($period ?? '') === '24h') selected @endif>Últimas 24 horas</option>
                        <option value="7d" @if(($period ?? '') === '7d') selected @endif>Últimos 7 días</option>
                        <option value="30d" @if(($period ?? '') === '30d') selected @endif>Últimos 30 días</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                </div>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="card-body">
        @if($logs->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Destinatario</th>
                            <th>Asunto</th>
                            <th class="text-center">Estado</th>
                            <th>Error</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td>
                                    <span class="fw-semibold d-block">{{ $log->created_at->format('d/m/Y') }}</span>
                                    <p class="text-muted">{{ $log->created_at->format('H:i:s') }}</p>
                                </td>
                                <td>
                                    <code class="bg-light px-2 py-1 rounded">{{ $log->recipient_email ?? 'N/A' }}</code>
                                </td>
                                <td>
                                    <small>{{ Str::limit($log->mailer_subject ?? 'Sin asunto', 40) }}</small>
                                </td>
                                <td class="text-center">
                                    @php
                                        $statusVal = is_object($log->status) ? $log->status->value : $log->status;
                                    @endphp
                                    @if($statusVal === 'success')
                                        <span class="badge rounded-pill py-1 px-2" style="background:#36c76c;color:#fff">Éxito</span>
                                    @elseif($statusVal === 'pending')
                                        <span class="badge rounded-pill py-1 px-2 bg-warning text-dark">Pendiente</span>
                                    @else
                                        <span class="badge rounded-pill py-1 px-2" style="background:#fa4c3c;color:#fff">Fallido</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->error_message)
                                        <p class="text-muted">{{ Str::limit($log->error_message, 50) }}</p>
                                    @else
                                        <p class="text-muted">-</p>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#logDetailModal{{ $log->id }}">
                                        Ver
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x mb-3 text-muted opacity-50"></i>
                <h6 class="mb-1">No hay logs registrados</h6>
                <p class="text-muted mb-0">
                    @if(!empty($searchEmail) || !empty($filterStatus) || !empty($period))
                        No se encontraron logs con los filtros seleccionados
                    @else
                        No hay registros de requests para este endpoint aún
                    @endif
                </p>
            </div>
        @endif
    </div>

    {{-- Pagination --}}
    @if($logs->hasPages())
        <div class="card-footer bg-white border-top">
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted">Mostrando {{ $logs->firstItem() }}-{{ $logs->lastItem() }} de {{ $logs->total() }} resultados</span>
                {{ $logs->appends(request()->input())->links() }}
            </div>
        </div>
    @endif
</div>

{{-- Log detail modals --}}
@foreach($logs as $log)
    <div class="modal fade" id="logDetailModal{{ $log->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold">Detalles del log #{{ $log->id }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    {{-- Info --}}
                    <div class="p-4 border-bottom bg-light">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <small class="text-muted fw-semibold text-uppercase d-block">Estado</small>
                                @php $statusVal = is_object($log->status) ? $log->status->value : $log->status; @endphp
                                @if($statusVal === 'success')
                                    <span class="badge rounded-pill py-1 px-2 mt-1" style="background:#36c76c;color:#fff">Éxito</span>
                                @elseif($statusVal === 'pending')
                                    <span class="badge rounded-pill py-1 px-2 mt-1 bg-warning text-dark">Pendiente</span>
                                @else
                                    <span class="badge rounded-pill py-1 px-2 mt-1" style="background:#fa4c3c;color:#fff">Fallido</span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted fw-semibold text-uppercase d-block">Fecha</small>
                                <span class="fw-semibold">{{ $log->created_at->format('d/m/Y H:i:s') }}</span>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted fw-semibold text-uppercase d-block">Destinatario</small>
                                <code class="bg-white px-2 py-1 rounded">{{ $log->recipient_email ?? 'N/A' }}</code>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted fw-semibold text-uppercase d-block">Asunto</small>
                                <span>{{ $log->mailer_subject ?? 'Sin asunto' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Payload --}}
                    <div class="p-4 border-bottom">
                        <h6 class="fw-bold mb-3"><i class="fas fa-code me-2"></i>Payload recibido</h6>
                        <pre class="bg-light p-3 rounded mb-0" style="max-height:300px;overflow-y:auto;font-size:12px;"><code>{{ json_encode($log->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                    </div>

                    {{-- Error --}}
                    @if($log->error_message)
                        <div class="p-4 border-bottom" style="background: rgba(220,53,69,0.10)">
                            <h6 class="fw-bold mb-3"><i class="fas fa-exclamation-circle me-2"></i>Mensaje de error</h6>
                            <pre class="bg-white p-3 rounded mb-0" style="max-height:200px;overflow-y:auto;font-size:12px;"><code>{{ $log->error_message }}</code></pre>
                        </div>
                    @endif

                    {{-- Metadata --}}
                    @if($log->job_id || $log->sent_at)
                        <div class="p-4 bg-light">
                            <div class="row g-3 small">
                                @if($log->job_id)
                                    <div class="col-md-6">
                                        <strong class="text-uppercase">Job ID:</strong>
                                        <code class="ms-2">{{ $log->job_id }}</code>
                                    </div>
                                @endif
                                @if($log->sent_at)
                                    <div class="col-md-6">
                                        <strong class="text-uppercase">Enviado:</strong>
                                        <span class="ms-2">{{ $log->sent_at->format('d/m/Y H:i:s') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary w-100 mb-2" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({ allowClear: false, width: '100%' });
    }
});
</script>
@endpush
