{{--
    Partial AJAX: busqueda + tabla + paginacion de paginas con datos de
    Google Search Console.

    Se incluye normalmente desde analytics.blade.php (dentro de #ajax-table-root)
    para el render inicial, y el controller devuelve ESTE mismo partial (sin
    layout) cuando la peticion es AJAX ($request->ajax()) al buscar o paginar.
    Ver public/managers/js/ajax-table.js.
--}}
<div class="card">
    <div class="card-body border-bottom">
        <form method="GET" action="{{ route('manager.seo.analytics') }}" id="searchForm">
            <div class="d-flex flex-column flex-lg-row gap-3 align-items-stretch">
                <div class="flex-fill">
                    <div class="input-group h-100">
                        <span class="input-group-text bg-white">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="search" name="search" class="form-control border-start-0 ps-0"
                               placeholder="Buscar por título o URL..."
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="d-flex gap-2 flex-shrink-0">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                    @if(request('search'))
                        <a href="{{ route('manager.seo.analytics') }}" class="btn btn-outline-secondary" title="Limpiar filtros">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <div class="card-body">
        @if($pages->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Página</th>
                            <th class="text-center">Clics</th>
                            <th class="text-center">Impresiones</th>
                            <th class="text-center">CTR</th>
                            <th class="text-center">Posición</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pages as $page)
                            @php
                                $ctr = $page->gsc_impressions > 0
                                    ? round($page->gsc_clicks / $page->gsc_impressions * 100, 2)
                                    : 0;
                                $posBadgeClass = match(true) {
                                    ($page->gsc_position ?? 999) <= 3  => 'pos-badge-excellent',
                                    ($page->gsc_position ?? 999) <= 10 => 'pos-badge-good',
                                    ($page->gsc_position ?? 999) <= 20 => 'pos-badge-fair',
                                    default                            => 'pos-badge-poor',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div class="small fw-semibold">{{ Str::limit($page->title ?? 'Sin título', 50) }}</div>
                                    @if($page->canonical_url)
                                        <small class="text-muted text-truncate d-block analytics-url-col">{{ $page->canonical_url }}</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="fw-semibold text-primary">{{ number_format($page->gsc_clicks ?? 0) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="small">{{ number_format($page->gsc_impressions ?? 0) }}</span>
                                </td>
                                <td class="text-center">
                                    @if($ctr < 2 && $page->gsc_impressions > 100)
                                        <span class="badge bg-danger-subtle text-danger fw-semibold">{{ $ctr }}%</span>
                                    @else
                                        <span class="badge bg-light text-dark">{{ $ctr }}%</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill text-white fw-bold {{ $posBadgeClass }}">
                                        #{{ number_format($page->gsc_position ?? 0, 1) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <a href="#" class="text-muted" data-bs-toggle="dropdown" data-bs-auto-close="true" data-bs-boundary="viewport">
                                            <i class="fas fa-ellipsis-vertical"></i>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('manager.seo.metas.edit', $page->id) }}">
                                                    Editar meta SEO
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
                <h6 class="mb-1">
                    @if(request('search'))
                        No se encontraron páginas
                    @else
                        Sin datos de Search Console
                    @endif
                </h6>
                <p class="text-muted mb-3">
                    @if(request('search'))
                        No hay resultados para los criterios de búsqueda
                    @else
                        Importa un CSV de Google Search Console para visualizar clics, impresiones y posiciones
                    @endif
                </p>
                @if(!request('search'))
                    <a href="{{ route('manager.seo.search-console.import') }}" class="btn btn-sm btn-primary">
                        Importar datos de Search Console
                    </a>
                @endif
            </div>
        @endif
    </div>

    @include('managers.includes.pagination-footer', [
        'paginator' => $pages,
        'itemLabel' => 'páginas',
    ])
</div>
