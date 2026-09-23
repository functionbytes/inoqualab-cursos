<div class="card">

            {{-- Stats --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Total eventos</h6>
                                <h4 class="mb-1 fw-bold" data-stat="total">{{ number_format($stats['total']) }}</h4>
                                <span class="text-muted">Registrados en el sistema</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Creaciones</h6>
                                <h4 class="mb-1 fw-bold" data-stat="created">{{ number_format($stats['created']) }}</h4>
                                <span class="text-muted">Eventos de creación</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Actualizaciones</h6>
                                <h4 class="mb-1 fw-bold" data-stat="updated">{{ number_format($stats['updated']) }}</h4>
                                <span class="text-muted">Eventos de modificación</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Eliminaciones</h6>
                                <h4 class="mb-1 fw-bold" data-stat="deleted">{{ number_format($stats['deleted']) }}</h4>
                                <span class="text-muted">Eventos de eliminación</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Busqueda y filtros --}}
            <div class="card-body border-bottom">
                @php
                    $advancedKeys = ['event', 'log_name', 'subject_type', 'causer', 'date_from', 'date_to'];
                    $activeFilterCount = collect($advancedKeys)->filter(fn ($k) => request()->filled($k))->count();
                    $hasAnyFilter = $activeFilterCount > 0 || request()->filled('search');
                @endphp

                <form method="GET" action="{{ route('manager.activity.index') }}" id="activity-filter-form">
                    {{-- Los avanzados viajan ocultos: el modal solo escribe en
                         ellos al aplicar, para que cerrar el modal sin aplicar
                         no cambie la busqueda. --}}
                    <input type="hidden" name="event"        id="filter-event"        value="{{ request('event') }}">
                    <input type="hidden" name="log_name"      id="filter-log-name"     value="{{ request('log_name') }}">
                    <input type="hidden" name="subject_type"  id="filter-subject-type" value="{{ request('subject_type') }}">
                    <input type="hidden" name="causer"        id="filter-causer"       value="{{ request('causer') }}">
                    <input type="hidden" name="date_from"     id="filter-date-from"    value="{{ request('date_from') }}">
                    <input type="hidden" name="date_to"       id="filter-date-to"      value="{{ request('date_to') }}">

                    <div class="d-flex align-items-center gap-2">
                        <input type="search" name="search" class="form-control flex-grow-1"
                               placeholder="Buscar en descripción o en los datos del cambio..."
                               value="{{ request('search') }}">

                        <button type="button" class="btn btn-secondary position-relative flex-shrink-0"
                                data-bs-toggle="modal" data-bs-target="#activity-filter-modal" title="Filtros avanzados">
                            <i class="fas fa-filter"></i>
                            @if($activeFilterCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary act-filter-badge">{{ $activeFilterCount }}</span>
                            @endif
                        </button>

                        <div class="d-flex gap-1 flex-shrink-0">
                            <button type="submit" class="btn btn-primary" title="Buscar">
                                <i class="fas fa-magnifying-glass"></i>
                            </button>
                            @if($hasAnyFilter)
                                <a href="{{ route('manager.activity.index') }}" class="btn btn-outline-secondary" title="Limpiar filtros">
                                    <i class="fas fa-xmark"></i>
                                </a>
                            @endif
                        </div>
                    </div>

                    @if($activeFilterCount > 0)
                        <div class="d-flex gap-2 flex-wrap mt-4 align-items-center">
                            <h6 class="mb-0">Filtrados:</h6>
                            @if(request('event'))
                                <span class="badge bg-primary-subtle text-primary py-1 px-2">Evento: {{ ucfirst(request('event')) }}</span>
                            @endif
                            @if(request('log_name'))
                                <span class="badge bg-primary-subtle text-primary py-1 px-2">Módulo: {{ ucfirst(request('log_name')) }}</span>
                            @endif
                            @if(request('subject_type'))
                                <span class="badge bg-primary-subtle text-primary py-1 px-2">Entidad: {{ class_basename(request('subject_type')) }}</span>
                            @endif
                            @if(request('causer'))
                                <span class="badge bg-primary-subtle text-primary py-1 px-2">Autor: {{ request('causer') }}</span>
                            @endif
                            @if(request('date_from'))
                                <span class="badge bg-primary-subtle text-primary py-1 px-2">Desde: {{ request('date_from') }}</span>
                            @endif
                            @if(request('date_to'))
                                <span class="badge bg-primary-subtle text-primary py-1 px-2">Hasta: {{ request('date_to') }}</span>
                            @endif
                        </div>
                    @endif
                </form>
            </div>

            {{-- Tabla --}}
            <div class="card-body">
                @if($logs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="3%"><input type="checkbox" id="select-all" class="form-check-input"></th>
                                    <th>Autor</th>
                                    <th>Evento</th>
                                    <th>Descripción</th>
                                    <th>Módulo</th>
                                    <th>Fecha</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                    @php
                                        $eventClass = match($log->event) {
                                            'created' => 'act-badge--created',
                                            'updated' => 'act-badge--updated',
                                            'deleted' => 'act-badge--deleted',
                                            default => 'act-badge--other',
                                        };
                                    @endphp
                                    <tr>
                                        <td><input type="checkbox" class="form-check-input bulk-checkbox" value="{{ $log->id }}"></td>
                                        <td>
                                            @if($log->causer)
                                                <div class="small fw-semibold">{{ trim(($log->causer->firstname ?? '').' '.($log->causer->lastname ?? '')) ?: 'Usuario #'.$log->causer_id }}</div>
                                                <small class="text-muted">{{ $log->causer->email ?? '' }}</small>
                                            @else
                                                <span class="badge bg-light text-dark border">Sistema</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge act-badge {{ $eventClass }}">{{ $log->event ?: 'n/a' }}</span>
                                        </td>
                                        <td>
                                            <small class="text-truncate d-block activity-description-col">{{ $log->description ?: '-' }}</small>
                                            @if($log->subject_type)
                                                <span class="badge bg-light text-dark border">
                                                    {{ class_basename($log->subject_type) }}{{ $log->subject_id ? ' #'.$log->subject_id : '' }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $log->log_name ?: 'default' }}</span>
                                        </td>
                                        <td>
                                            <div class="small">{{ $log->created_at?->format('d/m/Y H:i') }}</div>
                                            <small class="text-muted">{{ $log->created_at?->diffForHumans() }}</small>
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <a href="#" class="text-muted" data-bs-toggle="dropdown" data-bs-auto-close="true" data-bs-boundary="viewport">
                                                    <i class="fas fa-ellipsis-vertical"></i>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <button type="button" class="dropdown-item btn-detail"
                                                                data-props="{{ $log->properties?->toJson() }}"
                                                                data-meta="{{ class_basename($log->subject_type ?? '—').' #'.$log->subject_id }}">
                                                            Ver detalle
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
                        <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-history', 48) !!}</div>
                        <h5 class="fw-bold mb-2">
                            @if(request()->hasAny(['search', 'event', 'log_name', 'subject_type', 'causer', 'date_from', 'date_to']))
                                No se encontraron resultados
                            @else
                                Sin registros de actividad
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if(request('search'))
                                No hay resultados para "{{ request('search') }}"
                            @else
                                No hay eventos que coincidan con los filtros
                            @endif
                        </p>
                        @if(request()->hasAny(['search', 'event', 'log_name', 'subject_type', 'causer', 'date_from', 'date_to']))
                            <a href="{{ route('manager.activity.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar filtros</a>
                        @endif
                    </div>
                @endif
            </div>

            @include('managers.includes.pagination-footer', [
                'paginator' => $logs,
                'itemLabel' => 'registros',
            ])

        </div>
