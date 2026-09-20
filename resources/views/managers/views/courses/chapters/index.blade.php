@extends('layouts.managers')

@section('title', 'Temas')

@section('content')


    <div class="widget-content searchable-container list" id="courses-chapters-index"
         data-flash-success="{{ session('success') }}"
         data-config='@php $__jsonInline1 = [
            "courseSlack" => $course->slack,
            "nextPosition" => (int) $nextPosition,
            "routes" => [
                "bulkAction" => route("manager.courses.chapters.bulk-action"),
                "editBase" => url("panel/courses/chapters/edit"),
                "update" => route("manager.courses.chapters.update"),
                "store" => route("manager.courses.chapters.store"),
            ],
         ]; @endphp@json($__jsonInline1)'>

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Temas del curso</h5>
                        <p class="mb-0 text-muted">Gestiona los temas y su orden de aparición</p>
                    </div>
                    <div class="ms-auto">
                        <button type="button" class="btn btn-primary btn-new-chapter"
                                data-bs-toggle="modal" data-bs-target="#chapter-modal">
                            Nuevo tema
                        </button>
                    </div>
                </div>
            </div>

            {{-- Search + Filtros --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ Request::url() }}" id="searchForm">

                    <input type="hidden" name="available" id="filterAvailable" value="{{ $available ?? '' }}">

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

                        @php
                            $activeFilters = (int)(($available ?? '') !== '');
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
                @if($chapters->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="courses-col-checkbox">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>Título</th>
                                    <th class="text-center">Posición</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Actualización</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="chapters-sortable" data-reorder-url="{{ route('manager.courses.chapters.reorder') }}">
                                @foreach($chapters as $chapter)
                                    <tr data-id="{{ $chapter->id }}">
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox"
                                                   value="{{ $chapter->id }}">
                                        </td>
                                        <td>
                                            <div class="fw-semibold">
                                                <i class="fas fa-bars text-muted me-2 drag-handle" title="Arrastra para reordenar"></i>
                                                {{ Str::words($chapter->title, 8, '...') }}
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ $chapter->position }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($chapter->available)
                                                <span class="badge bg-success-subtle text-success">Publico</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Oculto</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ date('d/m/Y', strtotime($chapter->updated_at)) }}</span>
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
                                                        <a class="dropdown-item btn-edit-chapter" href="#"
                                                           data-slack="{{ $chapter->slack }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item btn-delete" href="#"
                                                           data-url="{{ route('manager.courses.chapters.destroy', $chapter->slack) }}"
                                                           data-title="Eliminar: {{ $chapter->title }}">
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
                        <i class="fas fa-layer-group fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">
                            @if(($searchKey ?? '') !== '' || ($available ?? '') !== '')
                                No se encontraron resultados
                            @else
                                No hay temas
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if(($searchKey ?? '') !== '' || ($available ?? '') !== '')
                                No hay temas que coincidan con los filtros aplicados.
                            @else
                                Crea el primer tema para este curso.
                            @endif
                        </p>
                        @if(($searchKey ?? '') !== '' || ($available ?? '') !== '')
                            <a href="{{ Request::url() }}" class="btn btn-outline-secondary">
                                Ver todos
                            </a>
                        @else
                            <button type="button" class="btn btn-primary btn-new-chapter"
                                    data-bs-toggle="modal" data-bs-target="#chapter-modal">
                                Nuevo tema
                            </button>
                        @endif
                    </div>
                @endif
            </div>

            @if($chapters->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        Mostrando {{ $chapters->firstItem() }}–{{ $chapters->lastItem() }} de {{ $chapters->total() }} temas
                    </span>
                    {{ $chapters->appends(request()->input())->links() }}
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
                        <label class="form-label fw-semibold">Estado</label>
                        <select id="modalAvailable" class="form-select">
                            <option value="">Todos</option>
                            <option value="1" {{ ($available ?? '') === '1' ? 'selected' : '' }}>Publico</option>
                            <option value="0" {{ ($available ?? '') === '0' ? 'selected' : '' }}>Oculto</option>
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

    {{-- Crear / Editar tema --}}
    <div class="modal fade" id="chapter-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="chapterModalTitle">Nuevo tema</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="chapterSlack" value="">
                    <p class="text-muted mb-3">
                        Completa los datos del tema. Los campos marcados con <span class="text-danger">*</span> son obligatorios.
                    </p>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="chapterTitle" maxlength="100" placeholder="Ingresar título">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Posición</label>
                            <input type="number" class="form-control" id="chapterPosition" min="1" placeholder="Ingresar posición">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                            <select class="select2 form-control" id="chapterAvailable">
                                @foreach($availables as $optId => $optLabel)
                                    <option value="{{ $optId }}">{{ $optLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Descripción</label>
                            <div class="quill-wrapper">
                                <div id="chapterDescription"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button type="button" id="btnSaveChapter" class="btn btn-primary w-100 mb-2">Guardar</button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'tema(s)',
        'bulkActions' => [
            ['value' => 'publish', 'label' => 'Publicar'],
            ['value' => 'hide', 'label' => 'Ocultar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    @include('managers.includes.delete')

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/courses/chapters/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/libs/jquery-ui/dist/jquery-ui.min.js') }}"></script>
<script src="{{ asset('managers/js/views/courses/chapters/index.js') }}"></script>
@endpush
