{{--
    Partial AJAX: filtros + tabla + paginacion del historial de auditorias SEO.

    Se incluye normalmente desde history.blade.php (dentro de #ajax-table-root)
    para el render inicial, y el controller devuelve ESTE mismo partial (sin
    layout) cuando la peticion es AJAX ($request->ajax()) al buscar, filtrar
    o paginar. Ver public/managers/js/ajax-table.js.
--}}
<div class="card">

    {{-- Stats --}}
    <div class="card-body border-bottom">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Total auditorías</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['total_audits'] ?? 0) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Score promedio</h6>
                        <h4 class="mb-1 fw-bold">
                            {{ isset($stats['avg_score']) && $stats['avg_score'] > 0 ? $stats['avg_score'] : '—' }}
                        </h4>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Grade A</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['grade_a'] ?? 0) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Grade F</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['grade_f'] ?? 0) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card-body border-bottom">
        <form method="GET" action="{{ route('manager.seo.audit.history') }}" id="searchForm">
            @php
                $filterChips = [];
                if ((request('grade') ?? '') !== '') {
                    $filterChips[] = [
                        'label' => 'Grade: ' . request('grade'),
                        'clear_url' => url()->current() . '?' . http_build_query(request()->except('grade')),
                    ];
                }
            @endphp
            <input type="hidden" name="grade" id="filterGrade" value="{{ request('grade') ?? '' }}">

            @php ob_start(); @endphp
            <div class="filter-popover-field">
                <div class="filter-popover-label">Grade</div>
                <div class="filter-popover-options">
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Grade" value="" {{ (request('grade') ?? '') === '' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Todos</span>
                    </label>
                    @foreach(['A', 'B', 'C', 'D', 'F'] as $g)
                        <label class="filter-popover-option">
                            <input type="radio" data-filter-name="popover_Grade" value="{{ $g }}" {{ request('grade') === $g ? 'checked' : '' }}>
                            <span class="filter-popover-dot"></span>
                            <span>{{ $g }}</span>
                        </label>
                    @endforeach
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
                            {{-- Sin dropdown de acciones: la fila abre la meta SEO
                                 vinculada directamente (cuando existe); eliminar se
                                 hace por selección + bulk ("Eliminar" en la barra
                                 inferior), ya no por fila. --}}
                            <tr @if($log->seoMeta) class="cursor-pointer" data-href="{{ route('manager.seo.audit.history.meta', $log->seoMeta->id) }}" title="Ver meta SEO" @endif>
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
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-history', 48) !!}</div>
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
