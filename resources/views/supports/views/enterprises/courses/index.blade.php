@extends('layouts.managers')

@section('content')

    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Cursos</h5>
                        <p class="mb-0 text-muted">Cursos asignados a {{ Str::words(Str::upper(Str::lower($enterprise->title)), 8, '...') }}</p>
                    </div>
                    <div class="ms-auto">
                        <a href="{{ route('support.enterprises.courses.assign', $enterprise->slack) }}" class="btn btn-primary">
                            Asignar cursos
                        </a>
                    </div>
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
                                <span class="text-muted">Cursos asignados</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Publicados</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['public']) }}</h4>
                                <span class="text-muted">Visibles</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Ocultos</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['hidden']) }}</h4>
                                <span class="text-muted">No visibles</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Search --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('support.enterprises.courses', $enterprise->slack) }}" id="searchForm">
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
                @if($courses->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Titulo</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($courses as $course)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ Str::words(Str::upper(Str::lower($course->title)), 12, '...') }}</div>
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
                                                           href="{{ route('support.enterprises.courses.view', [$enterprise->slack, $course->slack]) }}">
                                                            Detalle
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
                        <h5 class="fw-bold mb-2">
                            @if($searchKey ?? '')
                                No se encontraron resultados
                            @else
                                No hay cursos asignados
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if($searchKey ?? '')
                                No hay cursos que coincidan con la busqueda.
                            @else
                                Asigna el primer curso a esta empresa.
                            @endif
                        </p>
                        @if($searchKey ?? '')
                            <a href="{{ route('support.enterprises.courses', $enterprise->slack) }}" class="btn btn-outline-secondary">
                                Ver todos
                            </a>
                        @else
                            <a href="{{ route('support.enterprises.courses.assign', $enterprise->slack) }}" class="btn btn-primary">
                                Asignar cursos
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            @if($courses->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        Mostrando {{ $courses->firstItem() }}–{{ $courses->lastItem() }} de {{ $courses->total() }} cursos
                    </span>
                    {{ $courses->appends(request()->input())->links() }}
                </div>
            @endif

        </div>
    </div>

@endsection
