<div class="card">

    {{-- Stats --}}
    <div class="card-body border-bottom">
        <div class="row g-3">
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Total</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['total']) }}</h4>
                        <span class="text-muted">Inscripciones</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Culminadas</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['culminated']) }}</h4>
                        <span class="text-muted">Cursos finalizados</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Pendientes</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['pending']) }}</h4>
                        <span class="text-muted">En progreso</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search + Filtros --}}
    <div class="card-body border-bottom">
        @php
            $filterChips = [];
            if (($year ?? '') !== '' && ($year ?? null) !== null) {
                $filterChips[] = [
                    'label' => 'Año: ' . $year,
                    'clear_url' => url()->current() . '?' . http_build_query(request()->except('year')),
                ];
            }
            if (($culminated ?? '') !== '' && ($culminated ?? null) !== null) {
                $filterChips[] = [
                    'label' => 'Estado: ' . ($culminated == 1 ? 'Culminado' : 'Pendiente'),
                    'clear_url' => url()->current() . '?' . http_build_query(request()->except('culminated')),
                ];
            }
        @endphp
        <form method="GET" action="{{ route('support.enterprises.users.courses', $user->slack) }}" id="searchForm">
            <input type="hidden" name="year" id="filterYear" value="{{ $year ?? '' }}">
            <input type="hidden" name="culminated" id="filterCulminated" value="{{ $culminated ?? '' }}">

            @php ob_start(); @endphp
            <div class="filter-popover-field">
                <div class="filter-popover-label">Año</div>
                <div class="filter-popover-options">
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_year" value="" {{ ($year ?? '') === '' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Todos</span>
                    </label>
                    @foreach($years as $item)
                        <label class="filter-popover-option">
                            <input type="radio" data-filter-name="popover_year" value="{{ $item }}" {{ (string) ($year ?? '') === (string) $item ? 'checked' : '' }}>
                            <span class="filter-popover-dot"></span>
                            <span>{{ $item }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="filter-popover-field">
                <div class="filter-popover-label">Estado</div>
                <div class="filter-popover-options">
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_culminated" value="" {{ ($culminated ?? '') === '' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Todos</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_culminated" value="1" {{ ($culminated ?? '') === '1' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Culminado</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_culminated" value="0" {{ ($culminated ?? '') === '0' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Pendiente</span>
                    </label>
                </div>
            </div>
            @php $popoverBody = trim(ob_get_clean()); @endphp

            @include('managers.includes.filter-toolbar', [
                'searchName' => 'search',
                'searchValue' => $searchKey ?? '',
                'searchPlaceholder' => 'Buscar por titulo...',
                'popoverBody' => $popoverBody,
                'filterChips' => $filterChips,
            ])
        </form>
    </div>

    {{-- Tabla --}}
    <div class="card-body">
        @if($inscriptions->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle text-nowrap mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Titulo</th>
                            <th class="text-center">Ano</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inscriptions as $inscription)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ Str::words(Str::upper(Str::lower($inscription->course->title)), 12, '...') }}</div>
                                </td>
                                <td class="text-center">{{ date('Y', strtotime($inscription->enroll_start)) }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $inscription->culminated == 1 ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                        {{ $inscription->culminated == 1 ? 'Culminado' : 'Pendiente' }}
                                    </span>
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
                                                <a class="dropdown-item"
                                                   href="{{ route('support.enterprises.users.managements', $inscription->slack) }}">
                                                    Gestionar
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item"
                                                   href="{{ route('support.enterprises.courses.details', $inscription->slack) }}">
                                                    Detalle
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item"
                                                   href="{{ route('support.enterprises.courses.progress', $inscription->slack) }}">
                                                    Progreso
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
                <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-courses', 48) !!}</div>
                <h5 class="fw-bold mb-2">No hay cursos</h5>
                <p class="text-muted mb-0">Este usuario todavia no tiene cursos matriculados.</p>
            </div>
        @endif
    </div>

    @include('managers.includes.pagination-footer', [
        'paginator' => $inscriptions,
        'itemLabel' => 'cursos',
    ])

</div>
