@extends('layouts.managers')

@section('title', 'Anuncios')

@section('content')


    <div class="widget-content searchable-container list" id="courses-announcements-index"
         data-flash-success="{{ session('success') }}"
         data-config='@php $__jsonInline1 = [
            "courseSlack" => $course->slack,
            "routes" => [
                "bulkAction" => route("manager.courses.announcements.bulk-action"),
                "editBase" => url("panel/courses/announcements/edit"),
                "update" => route("manager.courses.announcements.update"),
                "store" => route("manager.courses.announcements.store"),
            ],
         ]; @endphp@json($__jsonInline1)'>

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Anuncios del curso</h5>
                        <p class="mb-0 text-muted">Gestiona los anuncios visibles para los estudiantes</p>
                    </div>
                    <div class="ms-auto">
                        <button type="button" class="btn btn-primary" id="btnNewAnnouncement"
                                data-bs-toggle="modal" data-bs-target="#announcement-modal">
                            Nuevo anuncio
                        </button>
                    </div>
                </div>
            </div>

            {{-- Search + Filtros --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('manager.courses.announcements', $course->slack) }}" id="searchForm">

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
                @if($announcements->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="courses-col-checkbox">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>Título</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Actualización</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($announcements as $announcement)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox"
                                                   value="{{ $announcement->id }}">
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ Str::words(Str::title(Str::lower($announcement->title)), 10, '...') }}</div>
                                        </td>
                                        <td class="text-center">
                                            @if($announcement->available)
                                                <span class="badge bg-success-subtle text-success">Publico</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Oculto</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ date('d/m/Y', strtotime($announcement->updated_at)) }}</span>
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
                                                        <a class="dropdown-item btn-edit-announcement" href="#"
                                                           data-slack="{{ $announcement->slack }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item btn-delete" href="#"
                                                           data-url="{{ route('manager.courses.announcements.destroy', $announcement->slack) }}"
                                                           data-title="Eliminar: {{ $announcement->title }}">
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
                        <i class="fas fa-bullhorn fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">
                            @if($searchKey || ($available ?? '') !== '')
                                No se encontraron resultados
                            @else
                                No hay anuncios
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if($searchKey || ($available ?? '') !== '')
                                No hay anuncios que coincidan con los filtros aplicados.
                            @else
                                Crea el primer anuncio para este curso.
                            @endif
                        </p>
                        @if($searchKey || ($available ?? '') !== '')
                            <a href="{{ route('manager.courses.announcements', $course->slack) }}" class="btn btn-outline-secondary">
                                Ver todos
                            </a>
                        @else
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#announcement-modal">
                                Nuevo anuncio
                            </button>
                        @endif
                    </div>
                @endif
            </div>

            @if($announcements->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        Mostrando {{ $announcements->firstItem() }}–{{ $announcements->lastItem() }} de {{ $announcements->total() }} anuncios
                    </span>
                    {{ $announcements->appends(request()->input())->links() }}
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
                    <a href="{{ route('manager.courses.announcements', $course->slack) }}" class="btn btn-secondary w-100">
                        Limpiar filtros
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Crear / Editar anuncio --}}
    <div class="modal fade" id="announcement-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="announcementModalTitle">Nuevo anuncio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="announcementSlack" value="">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="announcementTitle" maxlength="100" placeholder="Ingresar título">
                        <label id="announcementTitle-error" class="error d-none"></label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                        <select class="select2 form-control" id="announcementAvailable">
                            @foreach($availables as $optId => $optLabel)
                                <option value="{{ $optId }}">{{ $optLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Detalle</label>
                        <div class="quill-wrapper">
                            <div id="announcementDescription"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button type="button" id="btnSaveAnnouncement" class="btn btn-primary w-100 mb-2">Guardar</button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'anuncio(s)',
        'bulkActions' => [
            ['value' => 'publish', 'label' => 'Publicar'],
            ['value' => 'hide', 'label' => 'Ocultar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    @include('managers.includes.delete')

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/courses/announcements/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/courses/announcements/index.js') }}"></script>
@endpush
