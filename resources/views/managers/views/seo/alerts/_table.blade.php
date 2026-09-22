<div class="card">

            {{-- Header --}}
            

            {{-- Stats --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Sin revisar</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['unacknowledged']) }}</h4>
                                <span class="text-muted">Pendientes</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-danger-subtle h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2 text-danger">Criticas</h6>
                                <h4 class="mb-1 fw-bold text-danger">{{ number_format($stats['critical']) }}</h4>
                                <span class="text-muted">Alta prioridad</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-warning-subtle h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2 text-warning">Advertencias</h6>
                                <h4 class="mb-1 fw-bold text-warning">{{ number_format($stats['warning']) }}</h4>
                                <span class="text-muted">Media prioridad</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-info-subtle h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2 text-info">Informativas</h6>
                                <h4 class="mb-1 fw-bold text-info">{{ number_format($stats['info']) }}</h4>
                                <span class="text-muted">Baja prioridad</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Search + Filtros --}}
            <div class="card-body border-bottom">
                @php
                    $filterChips = [];
                    if (($severity ?? '') !== '') {
                        $__popoverLabels_Severity = ['critical' => 'Critica', 'warning' => 'Advertencia', 'info' => 'Informativa'];
                        $filterChips[] = [
                            'label' => 'Severidad: ' . ($__popoverLabels_Severity[$severity ?? ''] ?? ($severity ?? '')),
                            'clear_url' => url()->current() . '?' . http_build_query(request()->except('severity')),
                        ];
                    }
                    if (($status ?? '') !== '') {
                        $__popoverLabels_Status = ['pending' => 'Pendiente', 'acknowledged' => 'Revisada'];
                        $filterChips[] = [
                            'label' => 'Estado: ' . ($__popoverLabels_Status[$status ?? ''] ?? ($status ?? '')),
                            'clear_url' => url()->current() . '?' . http_build_query(request()->except('status')),
                        ];
                    }
                @endphp
                <form method="GET" action="{{ route('manager.seo.alerts.index') }}" id="searchForm">

                    <input type="hidden" name="severity" id="filterSeverity" value="{{ $severity ?? '' }}">
                    <input type="hidden" name="status"   id="filterStatus"   value="{{ $status ?? '' }}">

                    @php ob_start(); @endphp
                <div class="filter-popover-field">
                    <div class="filter-popover-label">Severidad</div>
                    <div class="filter-popover-options">
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Severity" value="" {{ ($severity ?? '') === '' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Todas</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Severity" value="critical" {{ ($severity ?? '') === 'critical'  ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Critica</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Severity" value="warning" {{ ($severity ?? '') === 'warning'   ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Advertencia</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Severity" value="info" {{ ($severity ?? '') === 'info'      ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Informativa</span>
                    </label>
                    </div>
                </div>
                <div class="filter-popover-field">
                    <div class="filter-popover-label">Estado</div>
                    <div class="filter-popover-options">
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Status" value="" {{ ($status ?? '') === '' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Todos</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Status" value="pending" {{ ($status ?? '') === 'pending'      ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Pendiente</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Status" value="acknowledged" {{ ($status ?? '') === 'acknowledged' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Revisada</span>
                    </label>
                    </div>
                </div>
@php $popoverBody = trim(ob_get_clean()); @endphp

                    @include('managers.includes.filter-toolbar', [
                        'searchName' => 'search',
                        'searchValue' => $search ?? '',
                        'searchPlaceholder' => 'Buscar por tipo o título...',
                        'popoverBody' => $popoverBody,
                        'filterChips' => $filterChips,
                    ])
                </form>
            </div>

            {{-- Tabla --}}
            <div class="card-body">
                @if($alerts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap">
                            <thead class="table-light">
                                <tr>
                                    <th class="col-checkbox">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>Severidad</th>
                                    <th>Tipo</th>
                                    <th>Título</th>
                                    <th>Mensaje</th>
                                    <th>URL</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($alerts as $alert)
                                    @php
                                        $severityMap = [
                                            'critical' => ['color' => 'danger',    'label' => 'Critica'],
                                            'warning'  => ['color' => 'warning',   'label' => 'Advertencia'],
                                            'info'     => ['color' => 'info',      'label' => 'Info'],
                                        ];
                                        $sev = $severityMap[$alert->severity] ?? ['color' => 'secondary', 'label' => $alert->severity];
                                    @endphp
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox"
                                                   value="{{ $alert->id }}">
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $sev['color'] }}-subtle text-{{ $sev['color'] }}">
                                                {{ $sev['label'] }}
                                            </span>
                                        </td>
                                        <td class="text-muted">{{ $alert->type }}</td>
                                        <td class="fw-semibold">{{ $alert->title }}</td>
                                        <td>
                                            <span class="text-muted" title="{{ $alert->message }}">
                                                {{ Str::limit($alert->message, 60) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($alert->url)
                                                <a href="{{ $alert->url }}" target="_blank" class="text-truncate d-block alert-url-truncate"
                                                   title="{{ $alert->url }}">
                                                    {{ Str::limit($alert->url, 30) }}
                                                </a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>{{ $alert->created_at->format('d/m/Y H:i') }}</div>
                                            <span class="text-muted">{{ $alert->created_at->diffForHumans() }}</span>
                                        </td>
                                        <td>
                                            @if($alert->acknowledged_at)
                                                <span class="badge bg-success-subtle text-success">Revisada</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Pendiente</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if(!$alert->acknowledged_at)
                                                <div class="dropdown">
                                                    <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0"
                                                            data-bs-toggle="dropdown"
                                                            data-bs-boundary="viewport">
                                                        <i class="fas fa-ellipsis-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <a class="dropdown-item acknowledge-btn" href="javascript:void(0)"
                                                               data-id="{{ $alert->id }}"
                                                               data-url="{{ route('manager.seo.alerts.acknowledge', $alert) }}">
                                                                Marcar como revisada
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item btn-delete" href="#"
                                                               data-url="{{ route('manager.seo.alerts.destroy', $alert) }}"
                                                               data-title="Eliminar alerta: {{ $alert->title }}">
                                                                Eliminar
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
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
                        <i class="fas fa-check-circle fa-3x mb-3 text-success opacity-50"></i>
                        <h5 class="fw-bold mb-2">Sin alertas pendientes</h5>
                        <p class="text-muted mb-4">
                            @if(($search ?? '') || ($severity ?? '') !== '' || ($status ?? '') !== '')
                                No se encontraron alertas con los filtros aplicados.
                            @else
                                No hay alertas SEO registradas en el sistema.
                            @endif
                        </p>
                        @if(($search ?? '') || ($severity ?? '') !== '' || ($status ?? '') !== '')
                            <a href="{{ route('manager.seo.alerts.index') }}" class="btn btn-outline-secondary">
                                Ver todas
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            @include('managers.includes.pagination-footer', [
                'paginator' => $alerts,
                'itemLabel' => 'alertas',
            ])

        </div>
