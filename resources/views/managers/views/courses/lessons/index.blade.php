@extends('layouts.managers')

@section('title', 'Clases')

@section('content')


    <div class="widget-content searchable-container list" id="courses-lessons-index"
         data-flash-success="{{ session('success') }}"
         data-config='@php $__jsonInline1 = [
            "nextPosition" => (int) $nextPosition,
            "routes" => [
                "bulkAction" => route("manager.courses.lessons.bulk-action"),
                "editBase" => url("panel/courses/lessons/edit"),
                "update" => route("manager.courses.lessons.update"),
                "store" => route("manager.courses.lessons.store"),
            ],
         ]; @endphp@json($__jsonInline1)'>

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Clases del curso</h5>
                        <p class="mb-0 text-muted">Gestiona las clases y su contenido</p>
                    </div>
                    <div class="ms-auto">
                        <button type="button" class="btn btn-primary btn-new-lesson"
                                data-bs-toggle="modal" data-bs-target="#lesson-modal">
                            Nueva clase
                        </button>
                    </div>
                </div>
            </div>

            {{-- Search + Filtros --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ Request::fullUrl() }}" id="searchForm">

                    <input type="hidden" name="available" id="filterAvailable" value="{{ $available ?? '' }}">
                    <input type="hidden" name="chapter"   id="filterChapter"   value="{{ $chapter ?? '' }}">
                    <input type="hidden" name="type"      id="filterType"      value="{{ $type ?? '' }}">

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
                            $activeFilters = (int)(($available ?? '') !== '') + (int)(($chapter ?? '') !== '') + (int)(($type ?? '') !== '');
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
                @if($lessons->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center"><input type="checkbox" class="form-check-input" id="select-all"></th>
                                    <th>Título</th>
                                    <th class="text-center">Posición</th>
                                    <th>Módulo</th>
                                    <th class="text-center">Tipo</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Actualización</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="lessons-sortable" data-reorder-url="{{ route('manager.courses.lessons.reorder') }}">
                                @foreach($lessons as $lesson)
                                    <tr data-id="{{ $lesson->id }}">
                                        <td class="text-center">
                                            <input type="checkbox" class="form-check-input bulk-checkbox" value="{{ $lesson->id }}">
                                        </td>
                                        <td>
                                            <div class="fw-semibold">
                                                <i class="fas fa-bars text-muted me-2 drag-handle" title="Arrastra para reordenar"></i>
                                                {{ Str::words($lesson->title, 8, '...') }}
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ $lesson->position }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $lesson->chapter->title }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary-subtle text-secondary">{{ $lesson->type->title }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($lesson->available)
                                                <span class="badge bg-success-subtle text-success">Público</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Oculto</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ $lesson->updated_at->format('d/m/Y') }}</span>
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
                                                        <a class="dropdown-item btn-edit-lesson" href="#"
                                                           data-slack="{{ $lesson->slack }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item btn-delete" href="#"
                                                           data-url="{{ route('manager.courses.lessons.destroy', $lesson->slack) }}"
                                                           data-title="Eliminar: {{ $lesson->title }}">
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
                        <i class="fas fa-play-circle fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">
                            @if($searchKey || $activeFilters > 0)
                                No se encontraron resultados
                            @else
                                No hay clases
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if($searchKey || $activeFilters > 0)
                                No hay clases que coincidan con los filtros aplicados.
                            @else
                                Crea la primera clase de este curso.
                            @endif
                        </p>
                        @if($searchKey || $activeFilters > 0)
                            <a href="{{ route('manager.courses.lessons', $course->slack) }}" class="btn btn-outline-secondary">
                                Ver todas
                            </a>
                        @else
                            <button type="button" class="btn btn-primary btn-new-lesson"
                                    data-bs-toggle="modal" data-bs-target="#lesson-modal">
                                Nueva clase
                            </button>
                        @endif
                    </div>
                @endif
            </div>

            @if($lessons->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        Mostrando {{ $lessons->firstItem() }}–{{ $lessons->lastItem() }} de {{ $lessons->total() }} clases
                    </span>
                    {{ $lessons->appends(request()->input())->links() }}
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
                        <label class="form-label fw-semibold">Estado</label>
                        <select id="modalAvailable" class="form-select">
                            <option value="">Todos</option>
                            <option value="1" {{ ($available ?? '') === '1' ? 'selected' : '' }}>Público</option>
                            <option value="0" {{ ($available ?? '') === '0' ? 'selected' : '' }}>Oculto</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Módulo</label>
                        <select id="modalChapter" class="form-select">
                            <option value="">Todos</option>
                            @foreach($chapters as $item)
                                <option value="{{ $item->id }}" {{ (isset($chapter) && $chapter == $item->id) ? 'selected' : '' }}>
                                    {{ $item->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Tipo</label>
                        <select id="modalType" class="form-select">
                            <option value="">Todos</option>
                            @foreach($types as $item)
                                <option value="{{ $item->id }}" {{ (isset($type) && $type == $item->id) ? 'selected' : '' }}>
                                    {{ $item->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button type="button" id="applyFiltersBtn" class="btn btn-primary w-100 mb-2">
                        Aplicar filtros
                    </button>
                    <a href="{{ route('manager.courses.lessons', $course->slack) }}" class="btn btn-secondary w-100">
                        Limpiar filtros
                    </a>
                </div>
            </div>
        </div>
    </div>

    @include('managers.includes.delete')

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'clase(s)',
        'bulkActions' => [
            ['value' => 'publish', 'label' => 'Publicar'],
            ['value' => 'hide', 'label' => 'Ocultar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    {{-- Crear / Editar clase --}}
    <div class="modal fade" id="lesson-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <form id="formLessons" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="lessonModalTitle">Nueva clase</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="lessonSlack" name="slack" value="">
                        <input type="hidden" name="course" value="{{ $course->slack }}">
                        <textarea class="d-none" id="detail" name="detail"></textarea>

                        <p class="text-muted mb-3">
                            El tipo de contenido (video, audio, imagen, PDF, etc.) determina qué campos deberás completar.
                        </p>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" maxlength="100" placeholder="Ingresar título">
                                <label id="title-error" class="error d-none" for="title"></label>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tema <span class="text-danger">*</span></label>
                                <select class="select2 form-control" id="chapter" name="chapter" data-placeholder="Selecciona un tema">
                                    @foreach($chapterOptions as $optId => $optLabel)
                                        <option value="{{ $optId }}">{{ $optLabel }}</option>
                                    @endforeach
                                </select>
                                <label id="chapter-error" class="error d-none" for="chapter"></label>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
                                <select class="select2 form-control" id="type" name="type" data-placeholder="Selecciona un tipo">
                                    @foreach($typeOptions as $optId => $optLabel)
                                        <option value="{{ $optId }}">{{ $optLabel }}</option>
                                    @endforeach
                                </select>
                                <label id="type-error" class="error d-none" for="type"></label>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Posición <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="position" name="position" placeholder="Ingresar posición">
                                <label id="position-error" class="error d-none" for="position"></label>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                                <select class="select2 form-control" id="available" name="available">
                                    @foreach($availables as $optId => $optLabel)
                                        <option value="{{ $optId }}">{{ $optLabel }}</option>
                                    @endforeach
                                </select>
                                <label id="available-error" class="error d-none" for="available"></label>
                            </div>

                            <div class="col-md-6 d-none divAudiovisual">
                                <label class="form-label fw-semibold">Plataforma <span class="text-danger">*</span></label>
                                <select class="select2 form-control" id="platform" name="platform" data-placeholder="Selecciona la plataforma">
                                    <option value=""></option>
                                    <option value="youtube">YouTube</option>
                                    <option value="vimeo">Vimeo</option>
                                </select>
                                <label id="platform-error" class="error d-none" for="platform"></label>
                            </div>

                            <div class="col-md-6 d-none divAudiovisual">
                                <label class="form-label fw-semibold">Link <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="url" name="url" placeholder="Ej: https://www.youtube.com/watch?v=... o https://vimeo.com/...">
                                <small id="url-platform" class="d-block mt-1"></small>
                                <label id="url-error" class="error d-none" for="url"></label>
                            </div>

                            <div class="col-md-6 d-none divAudiovisual divAudio">
                                <label class="form-label fw-semibold">Duración <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="duration" name="duration" placeholder="Ingresar duración">
                                <label id="duration-error" class="error d-none" for="duration"></label>
                            </div>

                            <div class="col-md-6 d-none divAudio divFiles">
                                <label class="form-label fw-semibold">Archivo</label>
                                <input type="file" class="form-control" id="file" name="file">
                                <small id="current-file-hint" class="form-text text-muted d-none"></small>
                                <label id="file-error" class="error d-none" for="file"></label>
                            </div>

                            <div class="col-md-6 d-none divFiles">
                                <label class="form-label fw-semibold">Tamaño</label>
                                <input type="text" class="form-control" id="size" name="size" readonly placeholder="Se calcula automáticamente">
                                <small class="form-text text-muted">Se completa solo al subir el archivo, en MB</small>
                                <label id="size-error" class="error d-none" for="size"></label>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Detalle</label>
                                <div class="quill-wrapper">
                                    <div id="details"></div>
                                </div>
                                <label id="detail-error" class="error d-none" for="detail"></label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer flex-column">
                        <button type="submit" class="btn btn-primary w-100 mb-2">Guardar</button>
                        <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/courses/lessons/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/libs/jquery-ui/dist/jquery-ui.min.js') }}"></script>
<script src="{{ asset('managers/js/views/courses/lessons/index.js') }}"></script>
@endpush
