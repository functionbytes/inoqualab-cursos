<div class="card">

            {{-- Header --}}
            

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
                @php
                    $filterChips = [];
                    if ((request('has_redirect') ?? '') !== '') {
                        $filterChips[] = [
                            'label' => 'Estado: ' . (request('has_redirect') === '1' ? 'Resueltos' : 'Sin resolver'),
                            'clear_url' => url()->current() . '?' . http_build_query(request()->except('has_redirect')),
                        ];
                    }
                @endphp
                <form method="GET" action="{{ route('manager.seo.logs.index') }}" id="searchForm">
                    <input type="hidden" name="has_redirect" id="filterHasRedirect" value="{{ request('has_redirect') ?? '' }}">

                    @php ob_start(); @endphp
                    <div class="filter-popover-field">
                        <div class="filter-popover-label">Estado</div>
                        <div class="filter-popover-options">
                            <label class="filter-popover-option">
                                <input type="radio" data-filter-name="popover_HasRedirect" value="" {{ (request('has_redirect') ?? '') === '' ? 'checked' : '' }}>
                                <span class="filter-popover-dot"></span>
                                <span>Todos</span>
                            </label>
                            <label class="filter-popover-option">
                                <input type="radio" data-filter-name="popover_HasRedirect" value="0" {{ request('has_redirect') === '0' ? 'checked' : '' }}>
                                <span class="filter-popover-dot"></span>
                                <span>Sin resolver</span>
                            </label>
                            <label class="filter-popover-option">
                                <input type="radio" data-filter-name="popover_HasRedirect" value="1" {{ request('has_redirect') === '1' ? 'checked' : '' }}>
                                <span class="filter-popover-dot"></span>
                                <span>Resueltos</span>
                            </label>
                        </div>
                    </div>
                    @php $popoverBody = trim(ob_get_clean()); @endphp

                    @include('managers.includes.filter-toolbar', [
                        'searchName' => 'search',
                        'searchValue' => request('search') ?? '',
                        'searchPlaceholder' => 'Buscar por URL...',
                        'popoverBody' => $popoverBody,
                        'filterChips' => $filterChips,
                    ])
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
                                            <span class="badge {{ $log->hit_count >= 10 ? 'bg-primary-subtle text-primary' : 'bg-secondary-subtle text-secondary' }}">
                                                {{ number_format($log->hit_count) }}
                                            </span>
                                        </td>
                                        <td>
                                            <p class="text-muted">{{ $log->last_seen_at?->diffForHumans() ?? '—' }}</p>
                                        </td>
                                        <td class="log-referer-col">
                                            <small class="text-muted text-break">{{ $log->referer ? Str::limit($log->referer, 50) : '—' }}</small>
                                        </td>
                                        <td>
                                            @if($log->has_redirect)
                                                <span class="badge bg-success-subtle text-success">Resuelto</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Sin redirect</span>
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
                                                            Eliminar registro
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
                        <div class="mb-3 text-success opacity-75">{!! \App\Html\IconHelper::render('empty-check', 48) !!}</div>
                        <h5 class="fw-bold mb-2">No hay errores 404 registrados</h5>
                        <p class="text-muted mb-4">El sitio no tiene errores 404 pendientes</p>
                    </div>
                @endif
            </div>

            @include('managers.includes.pagination-footer', [
                'paginator' => $logs,
                'itemLabel' => 'registros',
            ])

        </div>
