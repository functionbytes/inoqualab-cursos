@extends('layouts.managers')

@section('page_header')
    @include('supports.includes.card', [
        'title' => 'Inscripciones' . (isset($user) ? ' - '.$user->firstname.' '.$user->lastname : ''),
        'description' => 'Historial de matrículas a cursos de este usuario',
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Header --}}
            

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
                    if (($culminated ?? null) !== null) {
                        $filterChips[] = [
                            'label' => 'Estado: ' . ((string) $culminated === '1' ? 'Culminado' : 'Pendiente'),
                            'clear_url' => url()->current() . '?' . http_build_query(request()->except('culminated')),
                        ];
                    }
                @endphp
                <form method="GET" action="{{ Request::url() }}" id="searchForm">

                    <input type="hidden" name="culminated" id="filterCulminated" value="{{ $culminated ?? '' }}">

                    @php ob_start(); @endphp
                <div class="filter-popover-field">
                    <div class="filter-popover-label">Estado</div>
                    <div class="filter-popover-options">
                        <label class="filter-popover-option">
                            <input type="radio" data-filter-name="popover_Culminated" value="" {{ ($culminated ?? null) === null ? 'checked' : '' }}>
                            <span class="filter-popover-dot"></span>
                            <span>Todos</span>
                        </label>
                        <label class="filter-popover-option">
                            <input type="radio" data-filter-name="popover_Culminated" value="1" {{ (string) ($culminated ?? '') === '1' ? 'checked' : '' }}>
                            <span class="filter-popover-dot"></span>
                            <span>Culminado</span>
                        </label>
                        <label class="filter-popover-option">
                            <input type="radio" data-filter-name="popover_Culminated" value="0" {{ (string) ($culminated ?? '') === '0' ? 'checked' : '' }}>
                            <span class="filter-popover-dot"></span>
                            <span>Pendiente</span>
                        </label>
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
                @if($inscriptions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Curso</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Año</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($inscriptions as $inscription)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ Str::words(Str::upper(Str::lower($inscription->course->title)), 12, '...') }}</div>
                                        </td>
                                        <td class="text-center">
                                            @if($inscription->culminated == 1)
                                                <span class="badge bg-primary-subtle text-primary">Culminado</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Pendiente</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ date('Y', strtotime($inscription->enroll_start)) }}</span>
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
                                                           href="{{ route('support.users.inscriptions.edit', $inscription->slack) }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item confirm-delete"
                                                           href="#"
                                                           data-href="{{ route('support.users.courses.destroy', $inscription->slack) }}">
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
                        <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-courses', 48) !!}</div>
                        <h5 class="fw-bold mb-2">
                            @if(($searchKey ?? '') !== '' || ($culminated ?? null) !== null)
                                No se encontraron resultados
                            @else
                                No hay inscripciones
                            @endif
                        </h5>
                        <p class="text-muted mb-0">
                            @if(($searchKey ?? '') !== '' || ($culminated ?? null) !== null)
                                No hay inscripciones que coincidan con los filtros aplicados.
                            @else
                                Este usuario aún no tiene inscripciones registradas.
                            @endif
                        </p>
                    </div>
                @endif
            </div>

            @include('managers.includes.pagination-footer', [
                'paginator' => $inscriptions,
                'itemLabel' => 'inscripciones',
            ])

        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('supports/js/views/users/users/inscriptions/index.js') }}?v={{ @filemtime(public_path('supports/js/views/users/users/inscriptions/index.js')) ?: 1 }}"></script>
@endpush
