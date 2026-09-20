@extends('layouts.managers')

@section('title', 'Cursos de empresa')

@section('content')


    <div class="widget-content searchable-container list" id="enterprise-courses-page"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-bulk-url="{{ route('manager.enterprises.courses.bulk-action', $enterprise->slack) }}">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Cursos asignados</h5>
                        <p class="mb-0 text-muted">Gestiona los cursos vinculados a esta empresa</p>
                    </div>
                    <div class="ms-auto">
                        <a href="{{ route('manager.enterprises.courses.create', $enterprise->slack) }}" class="btn btn-primary">
                            Agregar curso
                        </a>
                    </div>
                </div>
            </div>

            {{-- Search --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('manager.enterprises.courses', $enterprise->slack) }}" id="searchForm">
                    <div class="d-flex gap-2 align-items-center">
                        <div class="flex-fill">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="search" name="search" class="form-control border-start-0 ps-0"
                                       placeholder="Buscar por título..."
                                       value="{{ $searchKey ?? '' }}">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary flex-shrink-0">
                            <i class="fas fa-search"></i>
                        </button>
                        @if($searchKey ?? '')
                            <a href="{{ route('manager.enterprises.courses', $enterprise->slack) }}"
                               class="btn btn-outline-secondary flex-shrink-0">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="card-body">
                @if($courses->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="col-checkbox">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>Título</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($courses as $course)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox"
                                                   value="{{ $course->id }}">
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ Str::words($course->title, 12, '...') }}</div>
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
                                                           href="{{ route('manager.enterprises.courses.view', [$enterprise->slack, $course->slack]) }}">
                                                            Dashboard
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item btn-delete" href="#"
                                                           data-url="{{ route('manager.enterprises.courses.destroy', [$enterprise->slack, $course->slack]) }}"
                                                           data-title="Eliminar: {{ $course->title }}">
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
                                No hay cursos que coincidan con la búsqueda.
                            @else
                                Agrega el primer curso a esta empresa.
                            @endif
                        </p>
                        @if($searchKey ?? '')
                            <a href="{{ route('manager.enterprises.courses', $enterprise->slack) }}" class="btn btn-outline-secondary">
                                Ver todos
                            </a>
                        @else
                            <a href="{{ route('manager.enterprises.courses.create', $enterprise->slack) }}" class="btn btn-primary">
                                Agregar curso
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

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'curso(s)',
        'bulkActions' => [
            ['value' => 'delete', 'label' => 'Quitar de la empresa'],
        ],
    ])

    @include('managers.includes.delete')

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/enterprises/courses/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/enterprises/courses/index.js') }}"></script>
@endpush
