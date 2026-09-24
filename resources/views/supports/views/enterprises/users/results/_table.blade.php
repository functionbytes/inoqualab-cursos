<div class="card">

    {{-- Stats --}}
    <div class="card-body border-bottom">
        <div class="row g-3">
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Total</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['total']) }}</h4>
                        <span class="text-muted">Registros</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Con examen</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['with_exam']) }}</h4>
                        <span class="text-muted">Con examen registrado</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Este ano</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['current_year']) }}</h4>
                        <span class="text-muted">Emitidos en {{ date('Y') }}</span>
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
            if (($course ?? '') !== '' && ($course ?? null) !== null) {
                $__popoverLabels_Course = $courses->pluck('title', 'id');
                $filterChips[] = [
                    'label' => 'Curso: ' . ($__popoverLabels_Course[$course ?? ''] ?? ($course ?? '')),
                    'clear_url' => url()->current() . '?' . http_build_query(request()->except('course')),
                ];
            }
        @endphp
        <form method="GET" action="{{ route('support.enterprises.users.results', $user->slack) }}" id="searchForm">

            <input type="hidden" name="year" id="filterYear" value="{{ $year ?? '' }}">
            <input type="hidden" name="course" id="filterCourse" value="{{ $course ?? '' }}">

            @php ob_start(); @endphp
        <div class="filter-popover-field">
            <div class="filter-popover-label">Año</div>
            <div class="filter-popover-options">
            <label class="filter-popover-option">
                <input type="radio" data-filter-name="popover_Year" value="" {{ (($year ?? '') === '' || ($year ?? null) === null) ? 'checked' : '' }}>
                <span class="filter-popover-dot"></span>
                <span>Todos</span>
            </label>
            @foreach($years as $item)
            <label class="filter-popover-option">
                <input type="radio" data-filter-name="popover_Year" value="{{ $item }}" {{ (isset($year) && $year == $item) ? 'checked' : '' }}>
                <span class="filter-popover-dot"></span>
                <span>{{ $item }}</span>
            </label>
            @endforeach
            </div>
        </div>
        <div class="filter-popover-field">
            <div class="filter-popover-label">Curso</div>
            <div class="filter-popover-options">
            <label class="filter-popover-option">
                <input type="radio" data-filter-name="popover_Course" value="" {{ (($course ?? '') === '' || ($course ?? null) === null) ? 'checked' : '' }}>
                <span class="filter-popover-dot"></span>
                <span>Todos</span>
            </label>
            @foreach($courses as $item)
            <label class="filter-popover-option">
                <input type="radio" data-filter-name="popover_Course" value="{{ $item->id }}" {{ (isset($course) && $course == $item->id) ? 'checked' : '' }}>
                <span class="filter-popover-dot"></span>
                <span>{{ $item->title }}</span>
            </label>
            @endforeach
            </div>
        </div>
            @php $popoverBody = trim(ob_get_clean()); @endphp

            @include('managers.includes.filter-toolbar', [
                'searchName' => 'search',
                'searchValue' => $searchKey ?? '',
                'searchPlaceholder' => 'Buscar por curso...',
                'popoverBody' => $popoverBody,
                'filterChips' => $filterChips,
            ])
        </form>
    </div>

    {{-- Tabla --}}
    <div class="card-body">
        @if($certificates->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle text-nowrap mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Curso</th>
                            <th class="text-center">Ano</th>
                            <th class="text-center">Fecha</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($certificates as $certificate)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ Str::words(Str::upper(Str::lower($certificate->course->title)), 12, '...') }}</div>
                                </td>
                                <td class="text-center">{{ date('Y', strtotime($certificate->start_at)) }}</td>
                                <td class="text-center">
                                    <span class="text-muted">{{ date('d/m/Y', strtotime($certificate->updated_at)) }}</span>
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
                                                   href="{{ route('support.enterprises.users.results.view', $certificate->slack) }}">
                                                    Visualizar
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item"
                                                   href="{{ route('support.enterprises.users.results.download', $certificate->slack) }}">
                                                    Descargar
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
                <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-results', 48) !!}</div>
                <h5 class="fw-bold mb-2">No hay resultados</h5>
                <p class="text-muted mb-0">Este usuario todavia no tiene resultados registrados.</p>
            </div>
        @endif
    </div>

    @include('managers.includes.pagination-footer', [
        'paginator' => $certificates,
        'itemLabel' => 'resultados',
    ])

</div>
