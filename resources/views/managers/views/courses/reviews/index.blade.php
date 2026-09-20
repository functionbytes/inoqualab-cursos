@extends('layouts.managers')

@section('title', 'Reseñas de cursos')

@section('content')


    <div class="widget-content searchable-container list" id="courses-reviews-index"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-config='@json([
            "routes" => [
                "bulkAction" => route("manager.reviews.bulk-action"),
            ],
         ])'>

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Reseñas de cursos</h5>
                        <p class="mb-0 text-muted">Gestiona las reseñas y calificaciones de los estudiantes</p>
                    </div>
                </div>
            </div>

            {{-- Search + Filtros --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ Request::url() }}" id="searchForm">

                    <input type="hidden" name="rating" id="filterRating" value="{{ $rating ?? '' }}">

                    <div class="d-flex gap-2 align-items-center">
                        <div class="flex-fill">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="search" name="search" class="form-control border-start-0 ps-0"
                                       placeholder="Buscar por curso, estudiante o comentario..."
                                       value="{{ $searchKey ?? '' }}">
                            </div>
                        </div>

                        @php
                            $activeFilters = (int)(($rating ?? '') !== '');
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
                @if($reviews->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="courses-col-checkbox">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>Curso</th>
                                    <th>Estudiante</th>
                                    <th class="text-center">Calificacion</th>
                                    <th>Comentario</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reviews as $review)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox"
                                                   value="{{ $review->id }}">
                                        </td>
                                        <td>
                                            <span class="fw-semibold">{{ Str::words(optional($review->course)->title ?? 'Curso eliminado', 8, '...') }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ trim((optional($review->user)->firstname ?? 'Estudiante') . ' ' . (optional($review->user)->lastname ?? '')) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-warning">
                                                @for($s = 1; $s <= 5; $s++)
                                                    <i class="fa-{{ $s <= $review->rating ? 'solid' : 'regular' }} fa-star"></i>
                                                @endfor
                                            </span>
                                        </td>
                                        <td class="reviews-comment-col">
                                            <span class="text-muted">{{ $review->comment ? Str::limit($review->comment, 120) : '—' }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ \Carbon\Carbon::parse($review->created_at)->format('d/m/Y') }}</span>
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
                                                        <a class="dropdown-item btn-delete" href="#"
                                                           data-url="{{ route('manager.reviews.destroy', $review->id) }}"
                                                           data-title="Eliminar reseña de {{ optional($review->user)->firstname ?? 'este estudiante' }}">
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
                        <i class="fas fa-star fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">
                            @if(($searchKey ?? '') || ($rating ?? '') !== '')
                                No se encontraron resultados
                            @else
                                No hay reseñas
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if(($searchKey ?? '') || ($rating ?? '') !== '')
                                No hay reseñas que coincidan con los filtros aplicados.
                            @else
                                Las reseñas aparecerán aquí cuando los estudiantes califiquen los cursos.
                            @endif
                        </p>
                        @if(($searchKey ?? '') || ($rating ?? '') !== '')
                            <a href="{{ Request::url() }}" class="btn btn-outline-secondary">
                                Ver todas
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            @if($reviews->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        Mostrando {{ $reviews->firstItem() }}–{{ $reviews->lastItem() }} de {{ $reviews->total() }} reseñas
                    </span>
                    {{ $reviews->appends(request()->input())->links() }}
                </div>
            @endif

        </div>
    </div>

    {{-- Filters modal --}}
    <div class="modal fade" id="filters-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Filtros</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Calificacion</label>
                        <select id="modalRating" class="form-select">
                            <option value="">Todas las calificaciones</option>
                            @for($r = 5; $r >= 1; $r--)
                                <option value="{{ $r }}" {{ ($rating ?? '') == $r ? 'selected' : '' }}>
                                    {{ $r }} estrella{{ $r === 1 ? '' : 's' }}
                                </option>
                            @endfor
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

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'reseña(s)',
        'bulkActions' => [
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    @include('managers.includes.delete')

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/courses/reviews/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/courses/reviews/index.js') }}"></script>
@endpush
