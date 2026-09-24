<div class="card">

    {{-- Stats --}}
    <div class="card-body border-bottom">
        <div class="row g-3">
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Total</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['total']) }}</h4>
                        <span class="text-muted">Certificados emitidos</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Vigentes</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['current']) }}</h4>
                        <span class="text-muted">Sin vencer</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Vencidos</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['expired']) }}</h4>
                        <span class="text-muted">Fuera de vigencia</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search + Filtros --}}
    <div class="card-body border-bottom">
        @php
            $filterChips = [];
            if (($course ?? '') !== '' && ($course ?? null) !== null) {
                $__popoverLabels_Course = $courses->pluck('title', 'id');
                $filterChips[] = [
                    'label' => 'Curso: ' . ($__popoverLabels_Course[$course ?? ''] ?? ($course ?? '')),
                    'clear_url' => url()->current() . '?' . http_build_query(request()->except('course')),
                ];
            }
        @endphp
        <form method="GET" action="{{ route('support.enterprises.users.certificates', $user->slack) }}" id="searchForm">

            <input type="hidden" name="course" id="filterCourse" value="{{ $course ?? '' }}">

            @php ob_start(); @endphp
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
                'searchPlaceholder' => 'Buscar...',
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
                                                   href="{{ route('support.enterprises.users.certificate.course', $certificate->slack) }}">
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
                <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-certificate', 48) !!}</div>
                <h5 class="fw-bold mb-2">No hay certificados</h5>
                <p class="text-muted mb-0">Este usuario todavia no tiene certificados emitidos.</p>
            </div>
        @endif
    </div>

    @include('managers.includes.pagination-footer', [
        'paginator' => $certificates,
        'itemLabel' => 'certificados',
    ])

</div>
