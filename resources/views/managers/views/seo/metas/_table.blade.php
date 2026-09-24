<div class="card">
            

            {{-- Stats --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Total registros</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['total']) }}</h4>
                                <p class="text-muted">Configuraciones meta SEO</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Indexables</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['indexable']) }}</h4>
                                <p class="text-muted">Visibles en buscadores</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">No indexables</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['noindex']) }}</h4>
                                <p class="text-muted">Bloqueadas con noindex</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Sin optimizar</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['missing_description'] + $stats['missing_og_image']) }}</h4>
                                <p class="text-muted">{{ $stats['missing_description'] }} sin desc. / {{ $stats['missing_og_image'] }} sin OG</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Score promedio</h6>
                                <h4 class="mb-1 fw-bold">{{ $stats['avg_score'] > 0 ? $stats['avg_score'] : '-' }}</h4>
                                <p class="text-muted">Puntuación SEO media</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="card-body border-bottom">
                @php
                    $sortByLabels = ['updated_at' => 'Actualizado', 'created_at' => 'Creado', 'title' => 'Título', 'seo_score' => 'Score SEO'];
                    $sortDirLabels = ['desc' => 'Descendente', 'asc' => 'Ascendente'];
                    $currentSortBy = request('sort_by', 'updated_at');
                    $currentSortDir = request('sort_direction', 'desc');

                    $filterChips = [];
                    if ((request('seoable_type') ?? '') !== '') {
                        $filterChips[] = [
                            'label' => 'Tipo: ' . class_basename(request('seoable_type')),
                            'clear_url' => url()->current() . '?' . http_build_query(request()->except('seoable_type')),
                        ];
                    }
                    if ($currentSortBy !== 'updated_at' || $currentSortDir !== 'desc') {
                        $filterChips[] = [
                            'label' => 'Orden: ' . $sortByLabels[$currentSortBy] . ' (' . $sortDirLabels[$currentSortDir] . ')',
                            'clear_url' => url()->current() . '?' . http_build_query(request()->except('sort_by', 'sort_direction')),
                        ];
                    }
                @endphp
                <form method="GET" action="{{ route('manager.seo.metas.index') }}" id="searchForm">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <input type="hidden" name="seoable_type" id="filterSeoableType" value="{{ request('seoable_type') ?? '' }}">
                    <input type="hidden" name="sort_by" id="filterSortBy" value="{{ $currentSortBy }}">
                    <input type="hidden" name="sort_direction" id="filterSortDirection" value="{{ $currentSortDir }}">

                    @php ob_start(); @endphp
                    <div class="filter-popover-field">
                        <div class="filter-popover-label">Tipo</div>
                        <div class="filter-popover-options">
                            <label class="filter-popover-option">
                                <input type="radio" data-filter-name="popover_SeoableType" value="" {{ (request('seoable_type') ?? '') === '' ? 'checked' : '' }}>
                                <span class="filter-popover-dot"></span>
                                <span>Todos</span>
                            </label>
                            @foreach($seoableTypes as $type)
                                <label class="filter-popover-option">
                                    <input type="radio" data-filter-name="popover_SeoableType" value="{{ $type }}" {{ request('seoable_type') === $type ? 'checked' : '' }}>
                                    <span class="filter-popover-dot"></span>
                                    <span>{{ class_basename($type) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="filter-popover-field">
                        <div class="filter-popover-label">Ordenar por</div>
                        <div class="filter-popover-options">
                            @foreach($sortByLabels as $value => $label)
                                <label class="filter-popover-option">
                                    <input type="radio" data-filter-name="popover_SortBy" value="{{ $value }}" {{ $currentSortBy === $value ? 'checked' : '' }}>
                                    <span class="filter-popover-dot"></span>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="filter-popover-field">
                        <div class="filter-popover-label">Dirección</div>
                        <div class="filter-popover-options">
                            @foreach($sortDirLabels as $value => $label)
                                <label class="filter-popover-option">
                                    <input type="radio" data-filter-name="popover_SortDirection" value="{{ $value }}" {{ $currentSortDir === $value ? 'checked' : '' }}>
                                    <span class="filter-popover-dot"></span>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    @php $popoverBody = trim(ob_get_clean()); @endphp

                    @include('managers.includes.filter-toolbar', [
                        'searchName' => 'search',
                        'searchValue' => request('search') ?? '',
                        'searchPlaceholder' => 'Buscar en título o descripción...',
                        'popoverBody' => $popoverBody,
                        'filterChips' => $filterChips,
                    ])
                </form>
            </div>

            {{-- Tabs --}}
            <ul class="nav nav-tabs border-0" id="seo-meta-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a href="{{ route('manager.seo.metas.index', array_merge(request()->except('tab', 'page'), ['tab' => 'all'])) }}"
                       class="nav-link rounded-0 py-3 {{ $tab === 'all' ? 'active' : '' }}">
                        Todas
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a href="{{ route('manager.seo.metas.index', array_merge(request()->except('tab', 'page'), ['tab' => 'indexable'])) }}"
                       class="nav-link rounded-0 py-3 {{ $tab === 'indexable' ? 'active' : '' }}">
                        Indexables
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a href="{{ route('manager.seo.metas.index', array_merge(request()->except('tab', 'page'), ['tab' => 'noindex'])) }}"
                       class="nav-link rounded-0 py-3 {{ $tab === 'noindex' ? 'active' : '' }}">
                        No indexables
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a href="{{ route('manager.seo.metas.index', array_merge(request()->except('tab', 'page'), ['tab' => 'unoptimized'])) }}"
                       class="nav-link rounded-0 py-3 {{ $tab === 'unoptimized' ? 'active' : '' }}">
                        Sin optimizar
                    </a>
                </li>
            </ul>

            {{-- Table --}}
            <div class="card-body">

                @php
                    $tabAlerts = [
                        'all'         => 'Los meta tags SEO se aplican automáticamente en las páginas públicas.',
                        'indexable'   => 'Estas configuraciones permiten que el contenido aparezca en resultados de búsqueda.',
                        'noindex'     => 'Estos contenidos están bloqueados para motores de búsqueda. Verifica que sea intencional.',
                        'unoptimized' => 'Estos registros carecen de descripción meta o imagen Open Graph.',
                    ];
                @endphp

                <div class="alert alert-info border-0 bg-info-subtle mb-3">
                    <small>{{ $tabAlerts[$tab] ?? $tabAlerts['all'] }}</small>
                </div>

                @if($metas->count() > 0)

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="30">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>Título</th>
                                    <th>Tipo</th>
                                    <th>Idioma</th>
                                    <th>Modelo</th>
                                    <th class="text-center">Robots</th>
                                    @if($tab === 'all' || $tab === 'indexable')
                                        <th class="text-center">Score</th>
                                    @endif
                                    @if($tab === 'unoptimized')
                                        <th class="text-center">Desc.</th>
                                        <th class="text-center">OG</th>
                                    @endif
                                    <th>Actualizado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metas as $meta)
                                    @php
                                        $robots = $meta->robots ?? 'index,follow';
                                        $hasNoindex = str_contains($robots, 'noindex');
                                        $hasNofollow = str_contains($robots, 'nofollow');
                                    @endphp
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox" value="{{ $meta->id }}">
                                        </td>
                                        <td>
                                            <span class="editable-cell d-block" data-meta-id="{{ $meta->id }}" data-field="title" data-original-value="{{ $meta->title ?? '' }}">
                                                <span class="cell-text">{{ Str::limit($meta->title ?? 'Sin título', 50) }}</span>
                                            </span>
                                            <span class="editable-cell" data-meta-id="{{ $meta->id }}" data-field="description" data-original-value="{{ $meta->description ?? '' }}">
                                                <small class="text-muted cell-text">{{ Str::limit($meta->description ?? 'Sin descripción', 60) }}</small>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $meta->short_type }}</span>
                                        </td>
                                        <td>
                                            @if($meta->locale)
                                                <span class="badge bg-primary-subtle text-primary">{{ strtoupper($meta->locale) }}</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Global</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ Str::limit($meta->seoable?->title ?? $meta->seoable?->name ?? '#'.$meta->seoable_id, 30) }}</small>
                                        </td>
                                        <td class="text-center">
                                            @if($hasNoindex)
                                                <span class="badge seo-badge seo-badge--negative">{{ $robots }}</span>
                                            @elseif($hasNofollow)
                                                <span class="badge seo-badge seo-badge--warning">{{ $robots }}</span>
                                            @else
                                                <span class="badge seo-badge seo-badge--positive">{{ $robots }}</span>
                                            @endif
                                        </td>
                                        @if($tab === 'all' || $tab === 'indexable')
                                            <td class="text-center">
                                                @if($meta->seo_score !== null)
                                                    @php
                                                        $score = $meta->seo_score;
                                                        // Tabla de colores del score, unica fuente de verdad: ver
                                                        // .seo-score--{a..f} en views/seo/metas/index.css. Antes usaba
                                                        // clases sueltas de Bootstrap (bg-success/bg-info/bg-warning/
                                                        // bg-orange/bg-danger) que el tema del panel resuelve con
                                                        // colores inconsistentes entre si (info=morado, danger=navy,
                                                        // etc.) y bg-orange ni siquiera existe -> el grado D quedaba
                                                        // invisible (blanco sobre blanco).
                                                        $scoreGrade = match(true) {
                                                            $score >= 90 => 'A',
                                                            $score >= 75 => 'B',
                                                            $score >= 60 => 'C',
                                                            $score >= 40 => 'D',
                                                            default      => 'F',
                                                        };
                                                    @endphp
                                                    <span class="badge seo-score seo-score--{{ strtolower($scoreGrade) }}" title="{{ $score }}">{{ $scoreGrade }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        @endif
                                        @if($tab === 'unoptimized')
                                            <td class="text-center">
                                                @if(empty($meta->description))
                                                    <i class="fas fa-times-circle text-dark"></i>
                                                @else
                                                    <i class="fas fa-check-circle text-success"></i>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if(empty($meta->og_image))
                                                    <i class="fas fa-times-circle text-dark"></i>
                                                @else
                                                    <i class="fas fa-check-circle text-success"></i>
                                                @endif
                                            </td>
                                        @endif
                                        <td>
                                            <p class="text-muted">{{ $meta->updated_at->diffForHumans() }}</p>
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <a href="#" class="text-muted" data-bs-toggle="dropdown">
                                                    <i class="fa fa-ellipsis-vertical"></i>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('manager.seo.metas.edit', $meta) }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item delete-btn"
                                                           data-bs-toggle="modal"
                                                           data-bs-target="#delete-modal"
                                                           data-url="{{ route('manager.seo.metas.destroy', $meta) }}"
                                                           data-title="Eliminar: {{ Str::limit($meta->title ?? 'Sin título', 30) }}">
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
                        <div class="mb-4">
                            @if($tab === 'unoptimized')
                                <i class="fas fa-check-circle fa-4x text-success opacity-50"></i>
                            @else
                                <i class="fas fa-tags fa-4x text-muted opacity-50"></i>
                            @endif
                        </div>
                        <h5 class="text-muted mb-2">
                            @if($tab === 'unoptimized')
                                Todas las configuraciones están optimizadas
                            @else
                                No hay registros para mostrar
                            @endif
                        </h5>
                    </div>
                @endif

            </div>

            @include('managers.includes.pagination-footer', [
                'paginator' => $metas,
                'itemLabel' => 'metas',
            ])

        </div>
