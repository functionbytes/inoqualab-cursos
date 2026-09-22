<div class="card">
        {{-- Header --}}
        

        {{-- Stats --}}
        <div class="card-body border-bottom">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="card bg-light h-100">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-2">Total endpoints</h6>
                            <h4 class="mb-1 fw-bold">{{ $stats['total'] }}</h4>
                            <p class="text-muted">Configurados</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light h-100">
                        <div class="card-body">
                            <h6 class="card-title mb-2">Activos</h6>
                            <h4 class="mb-1 fw-bold">{{ $stats['active'] }}</h4>
                            <p class="text-muted">En funcionamiento</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light h-100">
                        <div class="card-body">
                            <h6 class="card-title text-warning mb-2">Inactivos</h6>
                            <h4 class="mb-1 fw-bold">{{ $stats['inactive'] }}</h4>
                            <p class="text-muted">Desactivados</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light h-100">
                        <div class="card-body">
                            <h6 class="card-title text-info mb-2">Total requests</h6>
                            <h4 class="mb-1 fw-bold">{{ number_format($stats['total_requests']) }}</h4>
                            <p class="text-muted">Enviados</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Search & Filter --}}
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('mailers.endpoints.index') }}" id="searchForm">
                <div class="row g-2">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                            <input type="search" name="search" class="form-control"
                                   placeholder="Buscar por nombre o slug..."
                                   value="{{ $search ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="source" class="form-select select2">
                            <option value="">Todas las fuentes</option>
                            @foreach($sources as $src)
                                <option value="{{ $src }}" @if(($source ?? '') === $src) selected @endif>{{ $src }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select select2">
                            <option value="">Todos los estados</option>
                            <option value="active" @if(($status ?? '') === 'active') selected @endif>Activos</option>
                            <option value="inactive" @if(($status ?? '') === 'inactive') selected @endif>Inactivos</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Buscar</button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="card-body">
            @if($endpoints->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="col-checkbox">
                                    <input type="checkbox" class="form-check-input" id="select-all">
                                </th>
                                <th>Nombre</th>
                                <th>Fuente</th>
                                <th>Tipo</th>
                                <th class="text-center">Requests</th>
                                <th class="text-center">Éxito</th>
                                <th class="text-center">Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($endpoints as $endpoint)
                                @php
                                    $total = $endpoint->requests_count;
                                    $successRate = $total > 0 ? round(($endpoint->success_logs_count / $total) * 100, 1) : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input bulk-checkbox"
                                               value="{{ $endpoint->id }}">
                                    </td>
                                    <td>
                                        <span class="fw-bold d-block">{{ $endpoint->name }}</span>
                                        <p class="text-muted">{{ $endpoint->slug }}</p>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-primary rounded-pill py-1 px-2">{{ $endpoint->source }}</span>
                                    </td>
                                    <td>
                                        <code class="bg-light px-2 py-1 rounded">{{ $endpoint->type }}</code>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary-subtle text-dark rounded-pill py-1 px-2">{{ $total }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($total > 0)
                                            <div class="d-flex align-items-center justify-content-center gap-2">
                                                <small class="fw-semibold">{{ $successRate }}%</small>
                                                <div class="progress endpoint-progress">
                                                    <div class="progress-bar bg-success endpoint-progress-bar" role="progressbar"
                                                         data-width="{{ $successRate }}"
                                                         aria-valuenow="{{ $successRate }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($endpoint->is_active)
                                            <span class="badge rounded-pill py-1 px-2 bg-success text-white">Activo</span>
                                        @else
                                            <span class="badge rounded-pill py-1 px-2 bg-danger text-white">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <a href="#" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Acciones">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('mailers.endpoints.edit', $endpoint) }}">
                                                        Editar
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('mailers.endpoints.logs', $endpoint) }}">
                                                        Ver logs
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <button type="button" class="dropdown-item js-delete-endpoint"
                                                            data-delete-url="{{ route('mailers.endpoints.destroy', $endpoint) }}"
                                                            data-bs-toggle="modal" data-bs-target="#delete-modal">
                                                        Eliminar
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
                    <i class="fas fa-inbox fa-3x mb-3 text-muted opacity-50"></i>
                    <h6 class="mb-1">No hay endpoints configurados</h6>
                    <p class="text-muted mb-3">
                        @if(request('search'))
                            No se encontraron resultados para "{{ request('search') }}"
                        @else
                            Crea tu primer endpoint para gestionar emails desde aplicaciones externas
                        @endif
                    </p>
                    @if(!request('search'))
                        <a href="{{ route('mailers.endpoints.create') }}" class="btn btn-primary">
                            Crear endpoint
                        </a>
                    @endif
                </div>
            @endif
        </div>

        @include('managers.includes.pagination-footer', [
            'paginator' => $endpoints,
            'itemLabel' => 'endpoints',
        ])
    </div>
