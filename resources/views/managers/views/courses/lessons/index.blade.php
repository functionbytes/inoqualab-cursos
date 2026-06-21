@extends('layouts.managers')

@section('title', 'Clases')

@section('content')


    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Clases del curso</h5>
                        <p class="mb-0 text-muted">Gestiona las clases y su contenido</p>
                    </div>
                    <div class="ms-auto">
                        <a href="{{ route('manager.courses.lessons.create', $course->slack) }}" class="btn btn-primary">
                            Nueva clase
                        </a>
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
                        <button type="button" class="btn btn-outline-secondary flex-shrink-0"
                                data-bs-toggle="modal" data-bs-target="#filters-modal">
                            <i class="fas fa-sliders me-1"></i>
                            Filtros
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
                                    <th>Título</th>
                                    <th class="text-center">Posición</th>
                                    <th>Módulo</th>
                                    <th class="text-center">Tipo</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Actualización</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lessons as $lesson)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ Str::words($lesson->title, 8, '...') }}</div>
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
                                                        <a class="dropdown-item"
                                                           href="{{ route('manager.courses.lessons.edit', $lesson->slack) }}">
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
                            <a href="{{ route('manager.courses.lessons.index', $course->slack) }}" class="btn btn-outline-secondary">
                                Ver todas
                            </a>
                        @else
                            <a href="{{ route('manager.courses.lessons.create', $course->slack) }}" class="btn btn-primary">
                                Nueva clase
                            </a>
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
                    <a href="{{ route('manager.courses.lessons.index', $course->slack) }}" class="btn btn-secondary w-100">
                        Limpiar filtros
                    </a>
                </div>
            </div>
        </div>
    </div>

    @include('managers.includes.delete')

@endsection

@push('scripts')
<script>
$(function () {

    @if(session('success'))
        toastr.success('{{ session('success') }}');
    @endif

    // ── Filters modal ────────────────────────────────────────────────────────
    $('#applyFiltersBtn').on('click', function () {
        $('#filterAvailable').val($('#modalAvailable').val());
        $('#filterChapter').val($('#modalChapter').val());
        $('#filterType').val($('#modalType').val());
        $('#filters-modal').modal('hide');
        $('#searchForm').submit();
    });

    // ── Eliminar individual vía modal ────────────────────────────────────────
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $('#delete-modal .modal-title').text($btn.data('title'));
        $('#delete-form').attr('action', $btn.data('url'));
        $('#delete-modal').modal('show');
    });

});
</script>
@endpush
