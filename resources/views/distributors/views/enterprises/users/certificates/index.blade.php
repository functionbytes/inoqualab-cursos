@extends('layouts.managers')

@section('page_header')
    @php ob_start(); @endphp
    @if (count($certificates) > 1)
        <a href="{{ route('distributor.enterprises.users.certificate.broad', $user->slack) }}" class="btn btn-primary btn-icon" title="Descargar todos" aria-label="Descargar todos">
            <i class="fas fa-certificate"></i>
        </a>
    @endif
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('distributors.includes.card', [
        'title' => 'Certificados - '.$user->firstname.' '.$user->lastname,
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Search + Filtros --}}
            <div class="card-body border-bottom">
                @php
                    $filterChips = [];
                    if (($course ?? '') !== '' && ($course ?? null) !== null) {
                        $filterChips[] = [
                            'label' => 'Curso: '.optional($courses->firstWhere('id', $course))->title,
                            'clear_url' => url()->current().'?'.http_build_query(request()->except('course')),
                        ];
                    }
                @endphp
                <form method="GET" action="{{ route('distributor.enterprises.users.certificates', $user->slack) }}" id="searchForm">

                    <input type="hidden" name="course" id="filterCourse" value="{{ $course ?? '' }}">

                    @php ob_start(); @endphp
                    <div class="filter-popover-field">
                        <div class="filter-popover-label">Curso</div>
                        <div class="filter-popover-options">
                            <label class="filter-popover-option">
                                <input type="radio" data-filter-name="popover_course" value="" {{ ($course ?? '') === '' ? 'checked' : '' }}>
                                <span class="filter-popover-dot"></span>
                                <span>Todos</span>
                            </label>
                            @foreach($courses as $item)
                                <label class="filter-popover-option">
                                    <input type="radio" data-filter-name="popover_course" value="{{ $item->id }}" {{ (string) ($course ?? '') === (string) $item->id ? 'checked' : '' }}>
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
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-nowrap mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Curso</th>
                            <th>Ano</th>
                            <th>Fecha</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach ($certificates as $certificate)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ Str::words(Str::upper(Str::lower($certificate->course->title)), 12, '...') }}</div>
                                </td>
                                <td>{{ date('Y', strtotime($certificate->start_at)) }}</td>
                                <td>
                                    <span class="text-muted">{{ date('Y-m-d', strtotime($certificate->updated_at)) }}</span>
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
                                                <a class="dropdown-item" href="{{ route('distributor.enterprises.users.certificate.course', $certificate->slack) }}">Descargar</a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>
            </div>

            @include('managers.includes.pagination-footer', [
                'paginator' => $certificates,
                'itemLabel' => 'certificados',
            ])
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('distributors/js/enterprises/users/certificates/index.js') }}"></script>
@endpush

