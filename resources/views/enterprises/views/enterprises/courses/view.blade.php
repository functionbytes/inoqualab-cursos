@extends('layouts.managers')

@section('page_header')
    @include('enterprises.includes.card', ['title' => 'Usuarios - '.$course->title])
@endsection

@section('content')

    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Search + Filtros --}}
            <div class="card-body border-bottom">
                @php
                    $filterChips = [];
                    if (($year ?? '') !== '' && ($year ?? null) !== null) {
                        $filterChips[] = [
                            'label' => 'Año: '.$year,
                            'clear_url' => url()->current().'?'.http_build_query(request()->except('year')),
                        ];
                    }
                    if (($culminated ?? '') !== '' && ($culminated ?? null) !== null) {
                        $filterChips[] = [
                            'label' => 'Estado: '.($culminated == 1 ? 'Culminado' : 'Pendiente'),
                            'clear_url' => url()->current().'?'.http_build_query(request()->except('culminated')),
                        ];
                    }
                @endphp
                <form method="GET" action="{{ route('enterprise.courses.view', $course->slack) }}" id="searchForm">

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
                                <input type="radio" data-filter-name="popover_culminated" value="1" {{ (string) ($culminated ?? '') === '1' ? 'checked' : '' }}>
                                <span class="filter-popover-dot"></span>
                                <span>Culminado</span>
                            </label>
                            <label class="filter-popover-option">
                                <input type="radio" data-filter-name="popover_culminated" value="0" {{ (string) ($culminated ?? '') === '0' ? 'checked' : '' }}>
                                <span class="filter-popover-dot"></span>
                                <span>Pendiente</span>
                            </label>
                        </div>
                    </div>
                    @php $popoverBody = trim(ob_get_clean()); @endphp

                    @include('managers.includes.filter-toolbar', [
                        'searchName' => 'search',
                        'searchValue' => $searchKey ?? '',
                        'searchPlaceholder' => 'Buscar por nombre, correo o identificación...',
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
                                    <th>Identificación</th>
                                    <th>Cliente</th>
                                    <th>Año</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($inscriptions as $inscription)
                                    <tr>
                                        <td>{{ Str::upper($inscription->identification) }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ Str::words(Str::upper(Str::lower($inscription->firstname.' '.$inscription->lastname)), 12, '...') }}</div>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $inscription->enroll_culminated != null ? date('Y', strtotime($inscription->enroll_culminated)) : '—' }}</span>
                                        </td>
                                        <td>
                                            @if($inscription->culminated == 1)
                                                <span class="badge bg-success-subtle text-success">Culminado</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Pendiente</span>
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
                                                    @if($inscription->culminated == 1)
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('enterprise.users.certificate.user', $inscription->slack) }}">Certificado</a>
                                                        </li>
                                                    @endif
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('enterprise.courses.details', $inscription->slack) }}">Detalle</a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('enterprise.courses.progress', $inscription->slack) }}">Progreso</a>
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
                        <h5 class="fw-bold mb-2">
                            @if($searchKey || ($year ?? '') !== '' || ($culminated ?? '') !== '')
                                No se encontraron resultados
                            @else
                                No hay usuarios inscritos
                            @endif
                        </h5>
                        <p class="text-muted mb-0">
                            @if($searchKey || ($year ?? '') !== '' || ($culminated ?? '') !== '')
                                No hay usuarios que coincidan con los filtros aplicados.
                            @else
                                Aún no hay usuarios inscritos en este curso.
                            @endif
                        </p>
                    </div>
                @endif
            </div>

            @include('managers.includes.pagination-footer', [
                'paginator' => $inscriptions,
                'itemLabel' => 'usuarios',
            ])
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('enterprises/js/views/enterprises/courses/view.js') }}"></script>
@endpush
