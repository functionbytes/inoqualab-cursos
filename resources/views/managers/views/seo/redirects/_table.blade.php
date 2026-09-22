<div class="card">

            {{-- Header --}}
            

            {{-- Search + Filters --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('manager.seo.redirects.index') }}" id="searchForm">
                    <div class="row g-2">
                        <div class="col-12 col-lg">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="search" name="search" class="form-control border-start-0 ps-0"
                                       placeholder="Buscar por origen o destino..."
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-6 col-md-auto">
                            <select name="is_active" class="form-select">
                                <option value="">Todos los estados</option>
                                <option value="1" @selected(request('is_active') === '1')>Activos</option>
                                <option value="0" @selected(request('is_active') === '0')>Inactivos</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        @if(request('search') || (request('is_active') !== null && request('is_active') !== ''))
                            <div class="col-auto">
                                <a href="{{ route('manager.seo.redirects.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        @endif
                    </div>
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
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input toggle-active" type="checkbox"
                                                       data-id="{{ $redirect->id }}"
                                                       data-url="{{ route('manager.seo.redirects.toggle', $redirect->id) }}"
                                                       @checked($redirect->is_active)>
                                            </div>
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
                        <i class="fas fa-route fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">No hay redirects configurados</h5>
                        <p class="text-muted mb-4">Aun no hay redirects configurados</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRedirect">
                            Nuevo redirect
                        </button>
                    </div>
                @endif
            </div>

            @include('managers.includes.pagination-footer', [
                'paginator' => $redirects,
                'itemLabel' => 'redirects',
            ])

        </div>
