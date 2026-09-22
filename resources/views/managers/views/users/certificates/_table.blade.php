<div class="card">

            {{-- Header --}}
            

            {{-- Search + Filtros --}}
            <div class="card-body border-bottom">
                @php
                    $filterChips = [];
                    if (($course ?? '') !== '') {
                        $filterChips[] = [
                            'label' => 'Curso: ' . (optional($courses->firstWhere('id', $course))->title ?? $course),
                            'clear_url' => url()->current() . '?' . http_build_query(request()->except('course')),
                        ];
                    }
                @endphp
                <form method="GET" action="{{ Request::url() }}" id="searchForm">

                    <input type="hidden" name="course" id="filterCourse" value="{{ $course ?? '' }}">

                    @php ob_start(); @endphp
                <div class="filter-popover-field">
                    <div class="filter-popover-label">Curso</div>
                    <div class="filter-popover-options">
                        <label class="filter-popover-option">
                            <input type="radio" data-filter-name="popover_Course" value="" {{ ($course ?? '') === '' ? 'checked' : '' }}>
                            <span class="filter-popover-dot"></span>
                            <span>Todos los cursos</span>
                        </label>
                        @foreach($courses as $item)
                            <label class="filter-popover-option">
                                <input type="radio" data-filter-name="popover_Course" value="{{ $item->id }}" {{ ($course ?? '') == $item->id ? 'checked' : '' }}>
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
                                    <th class="text-center">Año</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($certificates as $certificate)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold">
                                                {{ Str::words(Str::title(Str::lower($certificate->course->title)), 12, '...') }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ date('Y', strtotime($certificate->start_at)) }}</span>
                                        </td>
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
                                                           href="{{ route('manager.certificate.course', $certificate->slack) }}">
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
                        <i class="fas fa-certificate fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">
                            @if(($searchKey ?? '') !== '' || ($course ?? '') !== '')
                                No se encontraron resultados
                            @else
                                No hay certificados
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if(($searchKey ?? '') !== '' || ($course ?? '') !== '')
                                No hay certificados que coincidan con los filtros aplicados.
                            @else
                                Los certificados aparecerán aquí cuando el usuario complete un curso.
                            @endif
                        </p>
                        @if(($searchKey ?? '') !== '' || ($course ?? '') !== '')
                            <a href="{{ Request::url() }}" class="btn btn-outline-secondary">
                                Ver todos
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            @include('managers.includes.pagination-footer', [
                'paginator' => $certificates,
                'itemLabel' => 'certificados',
            ])

        </div>
