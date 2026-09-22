<div class="card">

            {{-- Header --}}
            

            {{-- Stats --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-6 col-md-4">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Total</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($total) }}</h4>
                                <span class="text-muted">URLs configuradas</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Activas</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($totalActive) }}</h4>
                                <span class="text-muted">Incluidas en el sitemap</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Inactivas</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($totalInactive) }}</h4>
                                <span class="text-muted">Excluidas del sitemap</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Search --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('manager.seo.static-urls.index') }}" id="searchForm">
                    <div class="row g-2">
                        <div class="col-12 col-lg">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="search" name="search" class="form-control border-start-0 ps-0"
                                       placeholder="Buscar por URL o notas..."
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-6 col-md-auto">
                            <select name="status" class="form-select">
                                <option value="">Estado</option>
                                <option value="active" @selected(request('status') === 'active')>Activas</option>
                                <option value="inactive" @selected(request('status') === 'inactive')>Inactivas</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                        </div>
                        @if(request('search') || request('status'))
                            <div class="col-auto">
                                <a href="{{ route('manager.seo.static-urls.index') }}" class="btn btn-outline-secondary" title="Limpiar filtros">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="card-body">
                @if($staticUrls->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-nowrap">
                            <thead class="table-light">
                                <tr>
                                    <th class="col-checkbox">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>URL</th>
                                    <th class="text-center">Prioridad</th>
                                    <th class="text-center">Frecuencia</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($staticUrls as $staticUrl)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox" value="{{ $staticUrl->id }}">
                                        </td>
                                        <td>
                                            <code class="text-primary">{{ $staticUrl->url }}</code>
                                            @if($staticUrl->notes)
                                                <br><p class="text-muted">{{ $staticUrl->notes }}</p>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border">{{ number_format($staticUrl->priority, 1) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border">{{ $staticUrl->changefreq }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($staticUrl->is_active)
                                                <span class="badge bg-success-subtle text-success">Activa</span>
                                            @else
                                                <span class="badge bg-light text-dark border">Inactiva</span>
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
                                                        <a class="dropdown-item" href="{{ route('manager.seo.static-urls.edit', $staticUrl) }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item toggle-active" href="javascript:void(0)"
                                                           data-url="{{ route('manager.seo.static-urls.toggle', $staticUrl) }}">
                                                            {{ $staticUrl->is_active ? 'Desactivar' : 'Activar' }}
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item btn-delete" href="#"
                                                           data-url="{{ route('manager.seo.static-urls.destroy', $staticUrl) }}"
                                                           data-title="Eliminar: {{ $staticUrl->url }}">
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
                        <i class="fas fa-link fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">No hay URLs estáticas configuradas</h5>
                        <p class="text-muted mb-4">Comienza agregando tu primera URL estática</p>
                        <a href="{{ route('manager.seo.static-urls.create') }}" class="btn btn-primary">
                            Agregar primera URL
                        </a>
                    </div>
                @endif
            </div>

            @include('managers.includes.pagination-footer', [
                'paginator' => $staticUrls,
                'itemLabel' => 'registros',
            ])

        </div>
