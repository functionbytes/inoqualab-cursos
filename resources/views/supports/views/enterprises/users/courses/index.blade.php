@extends('layouts.managers')

@section('content')

    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div>
                    <h5 class="mb-1 fw-bold">Cursos</h5>
                    <p class="mb-0 text-muted">Cursos de {{ $user->firstname }} {{ $user->lastname }}</p>
                </div>
            </div>

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

            {{-- Search --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ Request::url() }}" id="searchForm">
                    <div class="d-flex gap-2 align-items-center">
                        <div class="flex-fill">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="search" name="search" class="form-control border-start-0 ps-0"
                                       placeholder="Buscar por titulo..."
                                       value="{{ $searchKey ?? '' }}">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary flex-shrink-0">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
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
                        <i class="fas fa-book-open fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">No hay cursos</h5>
                        <p class="text-muted mb-0">Este usuario todavia no tiene cursos matriculados.</p>
                    </div>
                @endif
            </div>

            @if($inscriptions->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        Mostrando {{ $inscriptions->firstItem() }}–{{ $inscriptions->lastItem() }} de {{ $inscriptions->total() }} cursos
                    </span>
                    {{ $inscriptions->appends(request()->input())->links() }}
                </div>
            @endif

        </div>
    </div>

@endsection
