@extends('layouts.managers')

@section('title', 'Meta SEO')

@section('content')

    <div class="widget-content searchable-container list">

        <div class="card">
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Panel de control SEO</h5>
                        <p class="small mb-0 text-muted">Auditoría centralizada de configuraciones SEO de todos los modelos del sistema</p>
                    </div>
                    <div class="ms-auto">
                        <div class="btn-group">
                            <button type="button" class="btn bg-primary-subtle text-primary dropdown-toggle"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Acciones
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="{{ route('manager.seo.metas.export') }}">Exportar CSV</a>
                                <a class="dropdown-item" href="{{ route('manager.seo.metas.import') }}">Importar CSV</a>
                                <a class="dropdown-item" href="{{ route('manager.seo.metas.export-json') }}">Exportar JSON</a>
                                <a class="dropdown-item" href="{{ route('manager.seo.metas.import-json') }}">Importar JSON</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('manager.seo.audit.index') }}">Auditoría SEO</a>
                                <button class="dropdown-item" type="button" data-action="reload">Actualizar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
                <form method="GET" action="{{ route('manager.seo.metas.index') }}" id="filter-form">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <div class="d-flex flex-column flex-lg-row gap-3 align-items-stretch">
                        <div class="flex-fill">
                            <div class="input-group h-100">
                                <span class="input-group-text bg-white">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="search"
                                       name="search"
                                       class="form-control border-start-0 ps-0"
                                       placeholder="Buscar en título o descripción..."
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="flex-shrink-0 filter-select-lg">
                            <select name="seoable_type" class="form-select">
                                <option value="">Todos los tipos</option>
                                @foreach($seoableTypes as $type)
                                    <option value="{{ $type }}" @selected(request('seoable_type') === $type)>
                                        {{ class_basename($type) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-shrink-0 filter-select-md">
                            <select name="sort_by" class="form-select">
                                <option value="updated_at" @selected(request('sort_by', 'updated_at') === 'updated_at')>Actualizado</option>
                                <option value="created_at" @selected(request('sort_by') === 'created_at')>Creado</option>
                                <option value="title" @selected(request('sort_by') === 'title')>Título</option>
                                <option value="seo_score" @selected(request('sort_by') === 'seo_score')>Score SEO</option>
                            </select>
                        </div>
                        <div class="flex-shrink-0 filter-select-sm">
                            <select name="sort_direction" class="form-select">
                                <option value="desc" @selected(request('sort_direction', 'desc') === 'desc')>Descendente</option>
                                <option value="asc" @selected(request('sort_direction') === 'asc')>Ascendente</option>
                            </select>
                        </div>
                        <div class="d-flex gap-2 flex-shrink-0">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                            @if(request()->hasAny(['search', 'seoable_type', 'sort_by', 'sort_direction']))
                                <a href="{{ route('manager.seo.metas.index', ['tab' => $tab]) }}"
                                   class="btn btn-outline-secondary"
                                   title="Limpiar filtros">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            {{-- Tabs --}}
            <ul class="nav nav-tabs border-0" id="seo-meta-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a href="{{ route('manager.seo.metas.index', array_merge(request()->except('tab', 'page'), ['tab' => 'all'])) }}"
                       class="nav-link rounded-0 py-3 {{ $tab === 'all' ? 'active' : '' }}">
                        Todas
                        <span class="badge bg-secondary ms-1">{{ $stats['total'] }}</span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a href="{{ route('manager.seo.metas.index', array_merge(request()->except('tab', 'page'), ['tab' => 'indexable'])) }}"
                       class="nav-link rounded-0 py-3 {{ $tab === 'indexable' ? 'active' : '' }}">
                        Indexables
                        <span class="badge bg-success ms-1">{{ $stats['indexable'] }}</span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a href="{{ route('manager.seo.metas.index', array_merge(request()->except('tab', 'page'), ['tab' => 'noindex'])) }}"
                       class="nav-link rounded-0 py-3 {{ $tab === 'noindex' ? 'active' : '' }}">
                        No indexables
                        <span class="badge bg-danger ms-1">{{ $stats['noindex'] }}</span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a href="{{ route('manager.seo.metas.index', array_merge(request()->except('tab', 'page'), ['tab' => 'unoptimized'])) }}"
                       class="nav-link rounded-0 py-3 {{ $tab === 'unoptimized' ? 'active' : '' }}">
                        Sin optimizar
                        <span class="badge bg-warning text-dark ms-1">{{ $stats['missing_description'] + $stats['missing_og_image'] }}</span>
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
                                        <input type="checkbox" class="form-check-input" id="select-all-metas">
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
                                                <span class="badge bg-primary">{{ strtoupper($meta->locale) }}</span>
                                            @else
                                                <span class="badge bg-secondary">Global</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ Str::limit($meta->seoable?->title ?? $meta->seoable?->name ?? '#'.$meta->seoable_id, 30) }}</small>
                                        </td>
                                        <td class="text-center">
                                            @if($hasNoindex)
                                                <span class="badge bg-danger-subtle text-danger">{{ $robots }}</span>
                                            @elseif($hasNofollow)
                                                <span class="badge bg-warning-subtle text-warning">{{ $robots }}</span>
                                            @else
                                                <span class="badge bg-success-subtle text-success">{{ $robots }}</span>
                                            @endif
                                        </td>
                                        @if($tab === 'all' || $tab === 'indexable')
                                            <td class="text-center">
                                                @if($meta->seo_score !== null)
                                                    @php
                                                        $score = $meta->seo_score;
                                                        $scoreBg = match(true) {
                                                            $score >= 90 => 'bg-success',
                                                            $score >= 75 => 'bg-info',
                                                            $score >= 60 => 'bg-warning',
                                                            $score >= 40 => 'bg-orange',
                                                            default      => 'bg-danger',
                                                        };
                                                        $scoreGrade = match(true) {
                                                            $score >= 90 => 'A',
                                                            $score >= 75 => 'B',
                                                            $score >= 60 => 'C',
                                                            $score >= 40 => 'D',
                                                            default      => 'F',
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $scoreBg }} text-white" title="{{ $score }}">{{ $scoreGrade }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        @endif
                                        @if($tab === 'unoptimized')
                                            <td class="text-center">
                                                @if(empty($meta->description))
                                                    <i class="fas fa-times-circle text-danger"></i>
                                                @else
                                                    <i class="fas fa-check-circle text-success"></i>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if(empty($meta->og_image))
                                                    <i class="fas fa-times-circle text-danger"></i>
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

            @if($metas->hasPages())
                <div class="card-footer">{{ $metas->links() }}</div>
            @endif

        </div>
    </div>

    @include('managers.includes.delete')

    <div id="metas-config" class="d-none"
         data-bulk-destroy-url="{{ route('manager.seo.metas.bulk-destroy') }}"
         data-inline-base-url="{{ url('panel/seo/metas') }}"></div>

    {{-- Bulk toolbar --}}
    <div id="bulk-toolbar" class="position-fixed bottom-0 start-50 translate-middle-x mb-4 d-none bulk-toolbar-float">
        <div class="card shadow-lg border-0">
            <div class="card-body py-2 px-4 d-flex align-items-center gap-3">
                <span class="text-muted small"><span data-bulk-count>0</span> seleccionados</span>
                <button type="button" class="btn btn-danger btn-sm" id="bulk-delete-btn">Eliminar</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="bulk-cancel">Cancelar</button>
            </div>
        </div>
    </div>

    {{-- Modal eliminación masiva --}}
    <div id="bulk-modal" class="modal fade">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center px-4 pb-2">
                    <div class="display-4 text-warning mb-3"><i class="fas fa-exclamation-triangle"></i></div>
                    <h4 class="my-0">¿Eliminar registros seleccionados?</h4>
                    <p class="text-muted mt-2">Se eliminarán <strong id="bulk-count">0</strong> registros meta SEO. Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer flex-column gap-1 border-0 pt-0">
                    <button type="button" id="bulk-delete-confirm" class="btn btn-danger w-100 mb-1">Confirmar eliminación</button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/seo/metas/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/seo/metas/index.js') }}"></script>
@endpush
