@extends('layouts.managers')

@section('content')

    <div class="widget-content searchable-container list"
         data-bulk-url="{{ route('support.users.courses.bulk-action') }}"
         data-bulk-entity-label="inscripción(es)">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div>
                    <h5 class="mb-1 fw-bold">Cursos inscritos{{ isset($user) ? ' - '.$user->firstname.' '.$user->lastname : '' }}</h5>
                    <p class="mb-0 text-muted">Cursos en los que este usuario está inscrito</p>
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
                                <h6 class="card-title mb-2">Culminados</h6>
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
                                       placeholder="Buscar por curso..."
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
                                    <th class="courses-col-checkbox">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
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
                                            <input type="checkbox" class="form-check-input bulk-checkbox"
                                                   value="{{ $inscription->id }}">
                                        </td>
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
                                                           href="{{ route('support.users.courses.postpone', $inscription->slack) }}">
                                                            Gestionar
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
                        <i class="fas fa-book-open fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">No hay cursos inscritos</h5>
                        <p class="text-muted mb-0">Este usuario aún no tiene cursos asignados.</p>
                    </div>
                @endif
            </div>

            @if($inscriptions->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        Mostrando {{ $inscriptions->firstItem() }}–{{ $inscriptions->lastItem() }} de {{ $inscriptions->total() }} inscripciones
                    </span>
                    {{ $inscriptions->appends(request()->input())->links() }}
                </div>
            @endif

        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'inscripción(es)',
        'bulkActions' => [
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('supports/css/views/users/courses/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('supports/js/views/users/courses/index.js') }}"></script>
@endpush
