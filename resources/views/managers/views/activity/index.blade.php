@extends('layouts.managers')

@section('title', 'Registro de actividad')

@section('content')

    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Registro de actividad</h5>
                        <p class="mb-0 text-muted">Auditoría de cambios: quién modificó qué y cuándo</p>
                    </div>
                </div>
            </div>

            {{-- Stats --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Total registros</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['total'] ?? 0) }}</h4>
                                <span class="text-muted">Eventos auditados</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Hoy</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['today'] ?? 0) }}</h4>
                                <span class="text-muted">Eventos de hoy</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Tipos de entidad</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['subjects'] ?? 0) }}</h4>
                                <span class="text-muted">Modelos auditados</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filtros --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('manager.activity.index') }}" id="filterForm">
                    <div class="row g-2">
                        <div class="col-6 col-md-2">
                            <select name="log_name" class="form-select">
                                <option value="">Todos los logs</option>
                                @foreach($logNames as $name)
                                    <option value="{{ $name }}" @selected(request('log_name') === $name)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <select name="event" class="form-select">
                                <option value="">Todos los eventos</option>
                                @foreach($events as $ev)
                                    <option value="{{ $ev }}" @selected(request('event') === $ev)>{{ ucfirst($ev) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <select name="subject_type" class="form-select">
                                <option value="">Todas las entidades</option>
                                @foreach($subjectTypes as $type)
                                    <option value="{{ $type }}" @selected(request('subject_type') === $type)>{{ class_basename($type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <input type="text" name="causer" class="form-control" placeholder="Autor (nombre/email)"
                                   value="{{ request('causer') }}">
                        </div>
                        <div class="col-6 col-md-2">
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-6 col-md-2">
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                        </div>
                        @if(request()->hasAny(['log_name', 'event', 'subject_type', 'causer', 'date_from', 'date_to']))
                            <div class="col-auto">
                                <a href="{{ route('manager.activity.index') }}" class="btn btn-outline-secondary">
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
                                    <th>Fecha</th>
                                    <th>Autor</th>
                                    <th>Evento</th>
                                    <th>Entidad</th>
                                    <th>Descripción</th>
                                    <th class="text-center">Detalle</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                    @php
                                        $eventBadge = match($log->event) {
                                            'created' => 'bg-success',
                                            'updated' => 'bg-warning text-dark',
                                            'deleted' => 'bg-danger',
                                            default => 'bg-secondary',
                                        };
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="d-block">{{ $log->created_at?->format('d/m/Y H:i') }}</span>
                                            <small class="text-muted">{{ $log->created_at?->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            @if($log->causer)
                                                <span class="d-block">{{ trim(($log->causer->firstname ?? '').' '.($log->causer->lastname ?? '')) ?: 'Usuario #'.$log->causer_id }}</span>
                                                <small class="text-muted">{{ $log->causer->email ?? '' }}</small>
                                            @else
                                                <span class="badge bg-light text-dark border">Sistema</span>
                                            @endif
                                        </td>
                                        <td><span class="badge {{ $eventBadge }}">{{ ucfirst($log->event ?: '—') }}</span></td>
                                        <td>
                                            @if($log->subject_type)
                                                <span class="d-block">{{ class_basename($log->subject_type) }}</span>
                                                <small class="text-muted">#{{ $log->subject_id }}</small>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="activity-description-col"><span class="text-break">{{ $log->description }}</span></td>
                                        <td class="text-center">
                                            @if($log->properties && $log->properties->isNotEmpty())
                                                <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0 btn-detail"
                                                        data-props="{{ $log->properties->toJson() }}"
                                                        data-meta="{{ class_basename($log->subject_type ?? '—').' #'.$log->subject_id }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-clock-rotate-left fa-3x mb-3 text-muted opacity-75"></i>
                        <h5 class="fw-bold mb-2">Sin registros de actividad</h5>
                        <p class="text-muted mb-4">No hay eventos que coincidan con los filtros</p>
                    </div>
                @endif
            </div>

            {{-- Paginación --}}
            @if($logs->hasPages())
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Mostrando {{ $logs->firstItem() }} - {{ $logs->lastItem() }} de {{ number_format($logs->total()) }}
                        </div>
                        <div>{{ $logs->appends(request()->input())->links() }}</div>
                    </div>
                </div>
            @endif

        </div>

    </div>

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

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/activity/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/activity/index.js') }}"></script>
@endpush
