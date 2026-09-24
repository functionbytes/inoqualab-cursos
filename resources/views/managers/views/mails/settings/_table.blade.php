<div class="card">

    {{-- Stats --}}
    <div class="card-body border-bottom">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Total</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['total']) }}</h4>
                        <span class="text-muted">Reglas configuradas</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Activas</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['active']) }}</h4>
                        <span class="text-muted">Confirmando correos</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Inactivas</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['inactive']) }}</h4>
                        <span class="text-muted">Pausadas</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Confianza promedio</h6>
                        <h4 class="mb-1 fw-bold">{{ $stats['total'] > 0 ? $stats['average'] . '%' : '—' }}</h4>
                        <span class="text-muted">Mínimo exigido</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search + Filters --}}
    <div class="card-body border-bottom">
        @php
            $statusLabels = ['1' => 'Activa', '0' => 'Inactiva'];
            $confidenceLabels = ['high' => 'Alta (90% o más)', 'medium' => 'Media (70–89%)', 'low' => 'Baja (menos de 70%)'];
            $filterChips = [];
            if ($status !== '') {
                $filterChips[] = [
                    'label' => 'Estado: ' . ($statusLabels[$status] ?? $status),
                    'clear_url' => url()->current() . '?' . http_build_query(request()->except('status')),
                ];
            }
            if ($confidence !== '') {
                $filterChips[] = [
                    'label' => 'Confianza: ' . ($confidenceLabels[$confidence] ?? $confidence),
                    'clear_url' => url()->current() . '?' . http_build_query(request()->except('confidence')),
                ];
            }
        @endphp
        <form method="GET" action="{{ route('manager.mails') }}" id="searchForm">

            <input type="hidden" name="status" id="filterStatus" value="{{ $status }}">
            <input type="hidden" name="confidence" id="filterConfidence" value="{{ $confidence }}">

            @php ob_start(); @endphp
            <div class="filter-popover-field">
                <div class="filter-popover-label">Estado</div>
                <div class="filter-popover-options">
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_status" value="" {{ $status === '' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Todos</span>
                    </label>
                    @foreach($statusLabels as $value => $label)
                        <label class="filter-popover-option">
                            <input type="radio" data-filter-name="popover_status" value="{{ $value }}" {{ $status === (string) $value ? 'checked' : '' }}>
                            <span class="filter-popover-dot"></span>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="filter-popover-field">
                <div class="filter-popover-label">Confianza mínima</div>
                <div class="filter-popover-options">
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_confidence" value="" {{ $confidence === '' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Todas</span>
                    </label>
                    @foreach($confidenceLabels as $value => $label)
                        <label class="filter-popover-option">
                            <input type="radio" data-filter-name="popover_confidence" value="{{ $value }}" {{ $confidence === $value ? 'checked' : '' }}>
                            <span class="filter-popover-dot"></span>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            @php $popoverBody = trim(ob_get_clean()); @endphp

            @include('managers.includes.filter-toolbar', [
                'searchName' => 'search',
                'searchValue' => $search,
                'searchPlaceholder' => 'Buscar por empresa...',
                'popoverBody' => $popoverBody,
                'filterChips' => $filterChips,
            ])
        </form>
    </div>

    {{-- Table --}}
    <div class="card-body">
        @if($rules->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th width="3%">
                                <input type="checkbox" id="select-all" class="form-check-input">
                            </th>
                            <th>Empresa</th>
                            <th>Confianza mínima</th>
                            <th>Estado</th>
                            <th>Creada</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rules as $rule)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input bulk-checkbox" value="{{ $rule->id }}">
                                </td>
                                <td>
                                    {{-- La empresa puede haberse borrado (soft delete) después de crear la regla --}}
                                    <div class="fw-semibold">{{ $rule->enterprise->title ?? 'Empresa eliminada' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info">≥ {{ $rule->min_confidence }}%</span>
                                </td>
                                <td>
                                    @if($rule->is_active)
                                        <span class="badge bg-success-subtle text-success">Activa</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">Inactiva</span>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $rule->created_at?->format('d/m/Y H:i') }}</div>
                                    <span class="text-muted">{{ $rule->created_at?->diffForHumans() }}</span>
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
                                                <a class="dropdown-item toggle-btn" href="#"
                                                   data-url="{{ route('manager.mails.rules.toggle', $rule) }}">
                                                    {{ $rule->is_active ? 'Desactivar' : 'Activar' }}
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item delete-btn" href="#"
                                                   data-url="{{ route('manager.mails.rules.destroy', $rule) }}"
                                                   data-title="Eliminar la regla de {{ $rule->enterprise->title ?? 'empresa eliminada' }}">
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
                <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-inbox', 48) !!}</div>
                <h5 class="fw-bold mb-2">
                    @if($search !== '' || $status !== '' || $confidence !== '')
                        No se encontraron resultados
                    @else
                        No hay reglas configuradas
                    @endif
                </h5>
                <p class="text-muted mb-4">
                    @if($search !== '' || $status !== '' || $confidence !== '')
                        Ninguna regla coincide con la búsqueda o los filtros
                    @else
                        Sin reglas, todos los correos entrantes pasan por revisión manual
                    @endif
                </p>
                @if($search !== '' || $status !== '' || $confidence !== '')
                    <a href="{{ route('manager.mails') }}" class="btn btn-secondary">Limpiar búsqueda</a>
                @else
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#rule-modal">
                        Nueva regla
                    </button>
                @endif
            </div>
        @endif
    </div>

    @include('managers.includes.pagination-footer', [
        'paginator' => $rules,
        'itemLabel' => 'reglas',
    ])

</div>
