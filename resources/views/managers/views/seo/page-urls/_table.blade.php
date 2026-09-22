<div class="card">

            {{-- Header --}}
            

            {{-- Stats --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Total páginas</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($totalPages) }}</h4>
                                <span class="text-muted">Páginas indexadas</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Con SEO</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($withSeo) }}</h4>
                                <span class="text-muted">SEO configurado</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Sin SEO</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($withoutSeo) }}</h4>
                                <span class="text-muted">Páginas huérfanas</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('manager.seo.page-urls.index') }}" id="filter-form">
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <div class="flex-fill">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="search" name="search" class="form-control border-start-0 ps-0"
                                       placeholder="Buscar por título o URL..."
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <select name="type" class="form-select">
                                <option value="">Todos los tipos</option>
                                <option value="Course"  @selected(request('type') === 'Course')>Curso</option>
                                <option value="Blog"    @selected(request('type') === 'Blog')>Blog</option>
                                <option value="Bundle"  @selected(request('type') === 'Bundle')>Bundle</option>
                            </select>
                        </div>
                        <div class="flex-shrink-0">
                            <select name="seo_status" class="form-select">
                                <option value="">Todos los estados SEO</option>
                                <option value="with_seo"    @selected(request('seo_status') === 'with_seo')>Con SEO</option>
                                <option value="without_seo" @selected(request('seo_status') === 'without_seo')>Sin SEO</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary flex-shrink-0">
                            <i class="fas fa-search"></i>
                        </button>
                        @if(request('search') || request('type') || request('seo_status'))
                            <a href="{{ route('manager.seo.page-urls.index') }}"
                               class="btn btn-outline-secondary flex-shrink-0" title="Limpiar filtros">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="card-body">
                @if($pages->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="3%">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>Título</th>
                                    <th class="text-center">Tipo</th>
                                    <th class="text-center">Estado SEO</th>
                                    <th class="text-center">Score</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pages as $page)
                                    @php
                                        $typeMap = [
                                            'Curso'  => ['Curso',  'bg-primary-subtle text-primary'],
                                            'Blog'   => ['Blog',   'bg-success-subtle text-success'],
                                            'Bundle' => ['Bundle', 'bg-warning-subtle text-warning'],
                                        ];
                                        [$typeLabel, $typeBadge] = $typeMap[$page['type'] ?? ''] ?? [($page['type'] ?? 'Página'), 'bg-secondary-subtle text-secondary'];

                                        $hasSeo = $page['has_seo'] ?? false;

                                        $scoreMap = [
                                            'A' => 'bg-success-subtle text-success',
                                            'B' => 'bg-info-subtle text-info',
                                            'C' => 'bg-warning-subtle text-warning',
                                            'D' => 'bg-danger-subtle text-danger',
                                            'F' => 'bg-danger-subtle text-danger',
                                        ];
                                        $scoreClass = $scoreMap[$page['seo_score'] ?? ''] ?? 'bg-secondary-subtle text-secondary';

                                    @endphp
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox"
                                                   data-model-class="{{ $page['model_class'] }}"
                                                   data-model-id="{{ $page['id'] }}"
                                                   @disabled($hasSeo)>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $page['title'] }}</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $typeBadge }} rounded-pill">{{ $typeLabel }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($hasSeo)
                                                <span class="badge bg-success-subtle text-success">
                                                    <i class="fas fa-check me-1"></i>Con SEO
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">
                                                    <i class="fas fa-times me-1"></i>Sin SEO
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($hasSeo && ! empty($page['seo_score']))
                                                <span class="badge {{ $scoreClass }} fw-bold">{{ $page['seo_score'] }}</span>
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
                                                    @if(! empty($page['url']))
                                                        <li>
                                                            <a class="dropdown-item" href="{{ $page['url'] }}"
                                                               target="_blank" rel="noopener">
                                                                Abrir URL
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                    @endif
                                                    @if($hasSeo && ! empty($page['seo_id']))
                                                        <li>
                                                            <a class="dropdown-item"
                                                               href="{{ route('manager.seo.metas.edit', $page['seo_id']) }}">
                                                                Editar SEO
                                                            </a>
                                                        </li>
                                                    @else
                                                        <li>
                                                            <a class="dropdown-item"
                                                               href="{{ route('manager.seo.orphans.index') }}">
                                                                Generar SEO
                                                            </a>
                                                        </li>
                                                    @endif
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item btn-create-redirect" href="#"
                                                           data-url="{{ $page['url'] ?? '' }}"
                                                           data-title="{{ $page['title'] ?? '' }}">
                                                            Crear redirect
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
                        <h5 class="fw-bold mb-2">
                            @if(request('search') || request('type') || request('seo_status'))
                                No se encontraron resultados
                            @else
                                No hay páginas registradas
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if(request('search') || request('type') || request('seo_status'))
                                No se encontraron páginas con los filtros aplicados
                            @else
                                No hay páginas indexadas disponibles
                            @endif
                        </p>
                        @if(request('search') || request('type') || request('seo_status'))
                            <a href="{{ route('manager.seo.page-urls.index') }}" class="btn btn-secondary">
                                Limpiar filtros
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            @if($pages instanceof \Illuminate\Pagination\LengthAwarePaginator)
                @include('managers.includes.pagination-footer', [
                    'paginator' => $pages,
                    'itemLabel' => 'páginas',
                ])
            @endif

        </div>
