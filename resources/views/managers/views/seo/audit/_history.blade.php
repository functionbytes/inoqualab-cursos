{{--
    Partial AJAX: filtros + tabla + paginacion del historial de auditorias SEO.

    Se incluye normalmente desde history.blade.php (dentro de #ajax-table-root)
    para el render inicial, y el controller devuelve ESTE mismo partial (sin
    layout) cuando la peticion es AJAX ($request->ajax()) al buscar, filtrar
    o paginar. Ver public/managers/js/ajax-table.js.
--}}
<div class="card">
    {{-- Filtros --}}
    <div class="card-body border-bottom">
        <form method="GET" action="{{ route('manager.seo.audit.history') }}" id="searchForm">
            <div class="row g-2">
                <div class="col-12 col-md">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="search" name="search" class="form-control border-start-0 ps-0"
                               placeholder="Buscar por URL..."
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-auto">
                    <select name="grade" class="form-select">
                        <option value="">Grade</option>
                        @foreach(['A', 'B', 'C', 'D', 'F'] as $g)
                            <option value="{{ $g }}" @selected(request('grade') === $g)>{{ $g }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                @if(request('search') || request('grade'))
                    <div class="col-auto">
                        <a href="{{ route('manager.seo.audit.history') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabla --}}
    <div class="card-body p-0">
        @if($logs->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle text-nowrap mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="3%">
                                <input type="checkbox" id="select-all" class="form-check-input">
                            </th>
                            <th>Meta / URL</th>
                            <th class="text-center">Score</th>
                            <th class="text-center">Grade</th>
                            <th class="text-center">Issues</th>
                            <th>Fecha auditoría</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            @php
                                $grade = $log->grade ?? 'F';
                                $gradeCls = match($grade) {
                                    'A'     => 'success',
                                    'B'     => 'primary',
                                    'C'     => 'warning',
                                    default => 'danger',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input bulk-checkbox"
                                           value="{{ $log->id }}">
                                </td>
                                <td class="audit-meta-col">
                                    @if($log->seoMeta)
                                        <span class="fw-semibold small d-block text-truncate">
                                            {{ $log->seoMeta->title ?: ('Meta #' . $log->seoMeta->id) }}
                                        </span>
                                        <code class="text-muted small">
                                            {{ Str::limit($log->url ?? $log->seoMeta->canonical_url ?? '—', 60) }}
                                        </code>
                                    @else
                                        <code class="small text-muted">{{ Str::limit($log->url ?? '—', 60) }}</code>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <strong>{{ $log->score ?? '—' }}</strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $gradeCls }}">{{ $grade }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ ($log->issues_count ?? 0) > 0 ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary' }}">
                                        {{ $log->issues_count ?? 0 }}
                                    </span>
                                </td>
                                <td>
                                    <p class="text-muted">
                                        {{ $log->created_at->format('d/m/Y H:i') }}
                                    </p>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0"
                                                data-bs-toggle="dropdown"
                                                data-bs-boundary="viewport">
                                            <i class="fas fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @if($log->seoMeta)
                                                <li>
                                                    <a class="dropdown-item"
                                                       href="{{ route('manager.seo.audit.history.meta', $log->seoMeta->id) }}">
                                                        Ver meta SEO
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                            @endif
                                            <li>
                                                <button type="button" class="dropdown-item btn-delete-log"
                                                        data-id="{{ $log->id }}"
                                                        data-url="{{ route('manager.seo.audit.history.destroy', $log->id) }}"
                                                        data-title="Eliminar auditoría del {{ $log->created_at->format('d/m/Y H:i') }}">
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
                <i class="fas fa-history fa-3x mb-3 text-muted opacity-50"></i>
                <h5 class="fw-bold mb-2">
                    @if(request('search') || request('grade'))
                        No se encontraron resultados
                    @else
                        Sin historial de auditorías
                    @endif
                </h5>
                <p class="text-muted mb-4">
                    @if(request('search') || request('grade'))
                        Intenta con otros filtros de búsqueda
                    @else
                        Ejecuta una auditoría para que aparezca el historial
                    @endif
                </p>
                @if(request('search') || request('grade'))
                    <a href="{{ route('manager.seo.audit.history') }}" class="btn btn-outline-secondary">
                        Limpiar filtros
                    </a>
                @else
                    <a href="{{ route('manager.seo.audit.index') }}" class="btn btn-primary">
                        Ir a auditoría
                    </a>
                @endif
            </div>
        @endif
    </div>

    @include('managers.includes.pagination-footer', [
        'paginator' => $logs,
        'itemLabel' => 'registros',
    ])
</div>
