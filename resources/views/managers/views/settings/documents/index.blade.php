@extends('layouts.managers')

@section('title', 'Documentos')

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/settings/documents/index.css') }}">
@endpush

@section('content')


    <div id="documentsPage" class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-bulk-action-url="{{ route('manager.documents.bulk-action') }}">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Documentos</h5>
                        <p class="mb-0 text-muted">Gestiona los documentos y archivos de la plataforma</p>
                    </div>
                    <div class="ms-auto">
                        @can('documents.create')
                        <a href="{{ route('manager.documents.create') }}" class="btn btn-primary">
                            Nuevo documento
                        </a>
                        @endcan
                    </div>
                </div>
            </div>

            {{-- Search + Filtros --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('manager.documents') }}" id="searchForm">

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
                @if($documents->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="documents-col-checkbox">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>Título</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Actualización</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($documents as $document)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox"
                                                   value="{{ $document->id }}">
                                        </td>
                                        <td class="fw-semibold">
                                            {{ Str::upper(Str::lower($document->title)) }}
                                        </td>
                                        <td class="text-center">
                                            @if($document->available)
                                                <span class="badge bg-success-subtle text-success">Público</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Oculto</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ \Carbon\Carbon::parse($document->updated_at)->format('d/m/Y') }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0"
                                                        data-bs-toggle="dropdown"
                                                        data-bs-boundary="viewport">
                                                    <i class="fas fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    @if($document->getFirstMedia('files'))
                                                        <li>
                                                            <a class="dropdown-item" target="_blank"
                                                               href="{{ $document->getFirstMedia('files')->getFullUrl() }}">
                                                                Visualizar
                                                            </a>
                                                        </li>
                                                    @endif
                                                    @can('documents.update')
                                                    <li>
                                                        <a class="dropdown-item"
                                                           href="{{ route('manager.documents.edit', $document->slack) }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    @endcan
                                                    @can('documents.delete')
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item btn-delete" href="#"
                                                           data-url="{{ route('manager.documents.destroy', $document->slack) }}"
                                                           data-title="Eliminar: {{ $document->title }}">
                                                            Eliminar
                                                        </a>
                                                    </li>
                                                    @endcan
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
                        <i class="fas fa-file-alt fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">
                            @if(($searchKey ?? '') !== '' || ($available ?? '') !== '')
                                No se encontraron resultados
                            @else
                                No hay documentos
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if(($searchKey ?? '') !== '' || ($available ?? '') !== '')
                                No hay documentos que coincidan con los filtros aplicados.
                            @else
                                Crea el primer documento de la plataforma.
                            @endif
                        </p>
                        @if(($searchKey ?? '') !== '' || ($available ?? '') !== '')
                            <a href="{{ route('manager.documents') }}" class="btn btn-outline-secondary">
                                Ver todos
                            </a>
                        @else
                            @can('documents.create')
                            <a href="{{ route('manager.documents.create') }}" class="btn btn-primary">
                                Nuevo documento
                            </a>
                            @endcan
                        @endif
                    </div>
                @endif
            </div>

            @if($documents->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        Mostrando {{ $documents->firstItem() }}–{{ $documents->lastItem() }} de {{ $documents->total() }} documentos
                    </span>
                    {{ $documents->appends(request()->input())->links() }}
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
                            <option value="1" {{ ($available ?? '') === '1' ? 'selected' : '' }}>Público</option>
                            <option value="0" {{ ($available ?? '') === '0' ? 'selected' : '' }}>Oculto</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button type="button" id="applyFiltersBtn" class="btn btn-primary w-100 mb-2">
                        Aplicar filtros
                    </button>
                    <a href="{{ route('manager.documents') }}" class="btn btn-secondary w-100">
                        Limpiar filtros
                    </a>
                </div>
            </div>
        </div>
    </div>

    @include('managers.includes.delete')

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'documento(s)',
        'bulkActions' => [
            ['value' => 'publish', 'label' => 'Publicar'],
            ['value' => 'hide', 'label' => 'Ocultar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/settings/documents/index.js') }}"></script>
@endpush
