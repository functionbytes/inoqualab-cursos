@extends('layouts.managers')

@section('page_header')
    @include('supports.includes.card', [
        'title' => 'Resultados',
        'description' => 'Resultados de ' . $user->firstname . ' ' . $user->lastname,
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
                <form method="GET" action="{{ Request::url() }}" id="searchForm">

                    <input type="hidden" name="year" id="filterYear" value="{{ $year ?? '' }}">
                    <input type="hidden" name="course" id="filterCourse" value="{{ $course ?? '' }}">

                    <div class="d-flex gap-2 align-items-center">
                        <div class="flex-fill">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="search" name="search" class="form-control border-start-0 ps-0"
                                       placeholder="Buscar por curso..."
                                       value="{{ $searchKey ?? '' }}">
                            </div>
                        </div>

                        @php
                            $activeFilters = (int)(($year ?? '') !== '' && ($year ?? null) !== null) + (int)(($course ?? '') !== '' && ($course ?? null) !== null);
                        @endphp
                        <button type="button" class="btn btn-outline-secondary flex-shrink-0" title="Filtros"
                                data-bs-toggle="modal" data-bs-target="#filters-modal">
                            <i class="fas fa-sliders"></i>
                            @if($activeFilters > 0)
                                <span class="badge bg-primary ms-1">{{ $activeFilters }}</span>
                            @endif
                        </button>

                        <button type="submit" class="btn btn-primary flex-shrink-0">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
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
                        <i class="fas fa-chart-simple fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">No hay resultados</h5>
                        <p class="text-muted mb-0">Este usuario todavia no tiene resultados registrados.</p>
                    </div>
                @endif
            </div>

            @if($certificates->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        Mostrando {{ $certificates->firstItem() }}–{{ $certificates->lastItem() }} de {{ $certificates->total() }} resultados
                    </span>
                    {{ $certificates->appends(request()->input())->links() }}
                </div>
            @endif

        </div>
    </div>

    {{-- Filters modal --}}
    <div class="modal fade" id="filters-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Filtros avanzados</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ano</label>
                        <select id="modalYear" class="form-select select2">
                            <option value="">Todos</option>
                            @foreach($years as $item)
                                <option value="{{ $item }}" {{ (isset($year) && $year == $item) ? 'selected' : '' }}>{{ $item }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Curso</label>
                        <select id="modalCourse" class="form-select select2">
                            <option value="">Todos</option>
                            @foreach($courses as $item)
                                <option value="{{ $item->id }}" {{ (isset($course) && $course == $item->id) ? 'selected' : '' }}>{{ $item->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button type="button" id="applyFiltersBtn" class="btn btn-primary w-100 mb-2">
                        Aplicar filtros
                    </button>
                    <a href="{{ Request::url() }}" class="btn btn-secondary w-100">
                        Limpiar filtros
                    </a>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('supports/js/enterprises/users/results/index.js') }}"></script>
@endpush
