{{--
    Partial AJAX: filtro + tabla + paginacion + modales de detalle de los
    logs de un endpoint de mailer.

    Los modales #logDetailModal{id} van DENTRO de este partial porque
    dependen de los IDs de $logs de la pagina actual — igual que en
    mailer/templates/_versions.blade.php.

    Se incluye normalmente desde logs.blade.php (dentro de #ajax-table-root)
    para el render inicial, y el controller devuelve ESTE mismo partial (sin
    layout) cuando la peticion es AJAX ($request->ajax()) al buscar, filtrar
    o paginar. Ver public/managers/js/ajax-table.js.
--}}
<div class="card">
    {{-- Filter --}}
    <div class="card-body border-bottom">
        <form method="GET" action="{{ route('mailers.endpoints.logs', $endpoint) }}" id="searchForm">
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
                                        <span class="badge rounded-pill py-1 px-2 bg-success text-white">Éxito</span>
                                    @elseif($statusVal === 'pending')
                                        <span class="badge rounded-pill py-1 px-2 bg-warning text-dark">Pendiente</span>
                                    @else
                                        <span class="badge rounded-pill py-1 px-2 bg-danger text-white">Fallido</span>
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

    @include('managers.includes.pagination-footer', [
        'paginator' => $logs,
        'itemLabel' => 'resultados',
    ])
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
                                    <span class="badge rounded-pill py-1 px-2 mt-1 bg-success text-white">Éxito</span>
                                @elseif($statusVal === 'pending')
                                    <span class="badge rounded-pill py-1 px-2 mt-1 bg-warning text-dark">Pendiente</span>
                                @else
                                    <span class="badge rounded-pill py-1 px-2 mt-1 bg-danger text-white">Fallido</span>
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
                        <pre class="bg-light p-3 rounded mb-0 log-detail-pre log-detail-pre-lg"><code>{{ json_encode($log->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                    </div>

                    {{-- Error --}}
                    @if($log->error_message)
                        <div class="p-4 border-bottom bg-danger-soft">
                            <h6 class="fw-bold mb-3"><i class="fas fa-exclamation-circle me-2"></i>Mensaje de error</h6>
                            <pre class="bg-white p-3 rounded mb-0 log-detail-pre"><code>{{ $log->error_message }}</code></pre>
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
