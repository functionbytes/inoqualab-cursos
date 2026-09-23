<div class="card">

            {{-- Header --}}
            

            {{-- Search + Filters --}}
            <div class="card-body border-bottom">
                @php
                    $filterChips = [];
                    if ((request('is_active') ?? '') !== '') {
                        $filterChips[] = [
                            'label' => 'Estado: ' . (request('is_active') === '1' ? 'Activos' : 'Inactivos'),
                            'clear_url' => url()->current() . '?' . http_build_query(request()->except('is_active')),
                        ];
                    }
                @endphp
                <form method="GET" action="{{ route('manager.seo.redirects.index') }}" id="searchForm">
                    <input type="hidden" name="is_active" id="filterIsActive" value="{{ request('is_active') ?? '' }}">

                    @php ob_start(); @endphp
                    <div class="filter-popover-field">
                        <div class="filter-popover-label">Estado</div>
                        <div class="filter-popover-options">
                            <label class="filter-popover-option">
                                <input type="radio" data-filter-name="popover_IsActive" value="" {{ (request('is_active') ?? '') === '' ? 'checked' : '' }}>
                                <span class="filter-popover-dot"></span>
                                <span>Todos</span>
                            </label>
                            <label class="filter-popover-option">
                                <input type="radio" data-filter-name="popover_IsActive" value="1" {{ request('is_active') === '1' ? 'checked' : '' }}>
                                <span class="filter-popover-dot"></span>
                                <span>Activos</span>
                            </label>
                            <label class="filter-popover-option">
                                <input type="radio" data-filter-name="popover_IsActive" value="0" {{ request('is_active') === '0' ? 'checked' : '' }}>
                                <span class="filter-popover-dot"></span>
                                <span>Inactivos</span>
                            </label>
                        </div>
                    </div>
                    @php $popoverBody = trim(ob_get_clean()); @endphp

                    @include('managers.includes.filter-toolbar', [
                        'searchName' => 'search',
                        'searchValue' => request('search') ?? '',
                        'searchPlaceholder' => 'Buscar por origen o destino...',
                        'popoverBody' => $popoverBody,
                        'filterChips' => $filterChips,
                    ])
                </form>
            </div>

            {{-- Table --}}
            <div class="card-body">
                @if($redirects->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="3%">
                                        <input type="checkbox" id="select-all" class="form-check-input">
                                    </th>
                                    <th>Origen</th>
                                    <th>Destino</th>
                                    <th>Codigo</th>
                                    <th>Tipo</th>
                                    <th>Hits</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($redirects as $redirect)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox"
                                                   value="{{ $redirect->id }}">
                                        </td>
                                        <td>
                                            <code class="small text-break">{{ $redirect->source_path }}</code>
                                        </td>
                                        <td>
                                            <code class="small text-break">{{ $redirect->target_path }}</code>
                                        </td>
                                        <td>
                                            <span class="badge {{ $redirect->status_code === 301 ? 'bg-primary' : 'bg-info' }}">
                                                {{ $redirect->status_code }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($redirect->is_regex)
                                                <span class="badge bg-warning-subtle text-warning">Regex</span>
                                            @elseif($redirect->is_wildcard)
                                                <span class="badge bg-secondary-subtle text-secondary">Wildcard</span>
                                            @else
                                                <span class="badge bg-light text-dark border">Exacto</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ number_format($redirect->hits_count ?? 0) }}</span>
                                        </td>
                                        <td>
                                            @if($redirect->is_active)
                                                <span class="badge bg-primary-subtle text-primary">Activo</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Inactivo</span>
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
                                                        <a class="dropdown-item toggle-active-link" href="#"
                                                           data-id="{{ $redirect->id }}"
                                                           data-url="{{ route('manager.seo.redirects.toggle', $redirect->id) }}">
                                                            {{ $redirect->is_active ? 'Desactivar' : 'Activar' }}
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item btn-edit-redirect" href="#"
                                                           data-id="{{ $redirect->id }}"
                                                           data-source="{{ $redirect->source_path }}"
                                                           data-target="{{ $redirect->target_path }}"
                                                           data-code="{{ $redirect->status_code }}"
                                                           data-regex="{{ (int) $redirect->is_regex }}"
                                                           data-wildcard="{{ (int) $redirect->is_wildcard }}"
                                                           data-note="{{ $redirect->note }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item btn-delete-redirect" href="#"
                                                           data-id="{{ $redirect->id }}"
                                                           data-url="{{ route('manager.seo.redirects.destroy', $redirect->id) }}">
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
                        <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-link', 48) !!}</div>
                        <h5 class="fw-bold mb-2">No hay redirects configurados</h5>
                        <p class="text-muted mb-4">Aun no hay redirects configurados</p>
                        <button type="button" class="btn btn-primary btn-icon" data-bs-toggle="modal" data-bs-target="#modalRedirect"
                                title="Nuevo redirect" aria-label="Nuevo redirect">{!! \App\Html\IconHelper::render('plus') !!}</button>
                    </div>
                @endif
            </div>

            @include('managers.includes.pagination-footer', [
                'paginator' => $redirects,
                'itemLabel' => 'redirects',
            ])

        </div>
