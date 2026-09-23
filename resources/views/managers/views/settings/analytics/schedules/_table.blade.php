<div class="card">

            {{-- Header --}}
            

            {{-- Stats --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Total</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['total']) }}</h4>
                                <span class="text-muted">Reportes configurados</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Activos</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['active']) }}</h4>
                                <span class="text-muted">Enviando reportes</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Inactivos</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['inactive']) }}</h4>
                                <span class="text-muted">Pausados</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Proximos 7 dias</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['pending']) }}</h4>
                                <span class="text-muted">Por enviar</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Search + Filters --}}
            <div class="card-body border-bottom">
                @php
                    $filterChips = [];
                    if (($frequency) !== '') {
                        $__popoverLabels_Frequency = ['daily' => 'Diario', 'weekly' => 'Semanal', 'monthly' => 'Mensual'];
                        $filterChips[] = [
                            'label' => 'Frecuencia: ' . ($__popoverLabels_Frequency[$frequency] ?? ($frequency)),
                            'clear_url' => url()->current() . '?' . http_build_query(request()->except('frequency')),
                        ];
                    }
                    if (($format) !== '') {
                        $__popoverLabels_Format = ['pdf' => 'PDF', 'excel' => 'Excel', 'csv' => 'CSV'];
                        $filterChips[] = [
                            'label' => 'Formato: ' . ($__popoverLabels_Format[$format] ?? ($format)),
                            'clear_url' => url()->current() . '?' . http_build_query(request()->except('format')),
                        ];
                    }
                    if (($status) !== '') {
                        $__popoverLabels_Status = ['1' => 'Activo', '0' => 'Inactivo'];
                        $filterChips[] = [
                            'label' => 'Estado: ' . ($__popoverLabels_Status[$status] ?? ($status)),
                            'clear_url' => url()->current() . '?' . http_build_query(request()->except('status')),
                        ];
                    }
                @endphp
                <form method="GET" action="{{ route('manager.settings.analytics.schedules.index') }}" id="searchForm">

                    {{-- Hidden inputs populated by the filters modal --}}
                    <input type="hidden" name="frequency" id="filterFrequency" value="{{ $frequency }}">
                    <input type="hidden" name="format"    id="filterFormat"    value="{{ $format }}">
                    <input type="hidden" name="status"    id="filterStatus"    value="{{ $status }}">

                    @php ob_start(); @endphp
                <div class="filter-popover-field">
                    <div class="filter-popover-label">Frecuencia</div>
                    <div class="filter-popover-options">
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Frequency" value="" {{ ($frequency) === '' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Todos</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Frequency" value="daily" {{ $frequency === 'daily'   ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Diario</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Frequency" value="weekly" {{ $frequency === 'weekly'  ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Semanal</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Frequency" value="monthly" {{ $frequency === 'monthly' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Mensual</span>
                    </label>
                    </div>
                </div>
                <div class="filter-popover-field">
                    <div class="filter-popover-label">Formato</div>
                    <div class="filter-popover-options">
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Format" value="" {{ ($format) === '' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Todos</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Format" value="pdf" {{ $format === 'pdf'   ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>PDF</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Format" value="excel" {{ $format === 'excel' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Excel</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Format" value="csv" {{ $format === 'csv'   ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>CSV</span>
                    </label>
                    </div>
                </div>
                <div class="filter-popover-field">
                    <div class="filter-popover-label">Estado</div>
                    <div class="filter-popover-options">
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Status" value="" {{ ($status) === '' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Todos</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Status" value="1" {{ $status === '1' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Activo</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Status" value="0" {{ $status === '0' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Inactivo</span>
                    </label>
                    </div>
                </div>
@php $popoverBody = trim(ob_get_clean()); @endphp

                    @include('managers.includes.filter-toolbar', [
                        'searchName' => 'search',
                        'searchValue' => $search,
                        'searchPlaceholder' => 'Buscar por nombre o email...',
                        'popoverBody' => $popoverBody,
                        'filterChips' => $filterChips,
                    ])
                </form>
            </div>

            {{-- Table --}}
            <div class="card-body">
                @if($schedules->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap">
                            <thead class="table-light">
                                <tr>
                                    <th width="3%">
                                        <input type="checkbox" id="select-all" class="form-check-input">
                                    </th>
                                    <th>Nombre</th>
                                    <th>Frecuencia</th>
                                    <th>Email</th>
                                    <th>Formato</th>
                                    <th>Estado</th>
                                    <th>Ultimo envio</th>
                                    <th>Proximo envio</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($schedules as $schedule)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox"
                                                   value="{{ $schedule->id }}">
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $schedule->name }}</div>
                                        </td>
                                        <td>
                                            @php
                                                $freqLabels = [
                                                    'daily'   => 'Diario',
                                                    'weekly'  => 'Semanal',
                                                    'monthly' => 'Mensual',
                                                ];
                                            @endphp
                                            <span class="badge bg-info-subtle text-info">
                                                {{ $freqLabels[$schedule->frequency] ?? $schedule->frequency }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $schedule->email }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary text-uppercase">
                                                {{ $schedule->format }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($schedule->is_active)
                                                <span class="badge bg-success-subtle text-success">Activo</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Inactivo</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($schedule->last_sent_at)
                                                <div>{{ $schedule->last_sent_at->format('d/m/Y H:i') }}</div>
                                                <span class="text-muted">{{ $schedule->last_sent_at->diffForHumans() }}</span>
                                            @else
                                                <span class="text-muted">Nunca</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($schedule->next_run_at)
                                                <div>{{ $schedule->next_run_at->format('d/m/Y H:i') }}</div>
                                                <span class="text-muted">{{ $schedule->next_run_at->diffForHumans() }}</span>
                                            @else
                                                <span class="text-muted">—</span>
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
                                                        <a class="dropdown-item"
                                                           href="{{ route('manager.settings.analytics.schedules.edit', $schedule) }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item toggle-btn" href="#"
                                                           data-url="{{ route('manager.settings.analytics.schedules.toggle', $schedule) }}"
                                                           data-active="{{ $schedule->is_active ? '1' : '0' }}">
                                                            {{ $schedule->is_active ? 'Desactivar' : 'Activar' }}
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item delete-btn" href="#"
                                                           data-url="{{ route('manager.settings.analytics.schedules.destroy', $schedule) }}"
                                                           data-title="Eliminar {{ $schedule->name }}">
                                                            Eliminar
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
                        <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-calendar', 48) !!}</div>
                        <h5 class="fw-bold mb-2">
                            @if($search)
                                No se encontraron resultados
                            @else
                                No hay reportes programados
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if($search)
                                No hay resultados para "{{ $search }}"
                            @else
                                Aun no hay reportes configurados
                            @endif
                        </p>
                        @if($search)
                            <a href="{{ route('manager.settings.analytics.schedules.index') }}"
                               class="btn btn-secondary">Limpiar busqueda</a>
                        @else
                            <a href="{{ route('manager.settings.analytics.schedules.create') }}"
                               class="btn btn-primary">
                                Nuevo reporte
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            @include('managers.includes.pagination-footer', [
                'paginator' => $schedules,
                'itemLabel' => 'reportes',
            ])

        </div>
