@extends('layouts.managers')

@section('title', 'Testimonios')

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/settings/testimonies/index.css') }}">
@endpush

@section('content')


    <div id="testimoniesPage" class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-bulk-action-url="{{ route('manager.testimonies.bulk-action') }}">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Testimonios</h5>
                        <p class="mb-0 text-muted">Gestiona los testimonios visibles en la plataforma</p>
                    </div>
                    <div class="ms-auto">
                        @can('testimonies.create')
                        <a href="{{ route('manager.testimonies.create') }}" class="btn btn-primary">
                            Nuevo testimonio
                        </a>
                        @endcan
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
                                       placeholder="Buscar por nombre..."
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
                @if($testimonies->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap">
                            <thead class="table-light">
                                <tr>
                                    <th class="testimonies-col-checkbox">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>Titulo</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($testimonies as $testimonie)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox"
                                                   value="{{ $testimonie->id }}">
                                        </td>
                                        <td>
                                            @if($testimonie->icon)
                                                <i class="{{ $testimonie->icon }} text-muted me-1"></i>
                                            @endif
                                            <span class="fw-semibold">{{ $testimonie->firstname . ' ' . $testimonie->lastname }}</span>
                                            @if($testimonie->role)
                                                <br><span class="text-muted small">{{ $testimonie->role }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($testimonie->available == 1)
                                                <span class="badge bg-success-subtle text-success">Publico</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Oculto</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ date('d/m/Y', strtotime($testimonie->updated_at)) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0"
                                                        data-bs-toggle="dropdown"
                                                        data-bs-boundary="viewport">
                                                    <i class="fas fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    @can('testimonies.update')
                                                    <li>
                                                        <a class="dropdown-item"
                                                           href="{{ route('manager.testimonies.edit', $testimonie->slack) }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    @endcan
                                                    @can('testimonies.delete')
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item btn-delete" href="#"
                                                           data-url="{{ route('manager.testimonies.destroy', $testimonie->slack) }}"
                                                           data-title="Eliminar: {{ $testimonie->firstname . ' ' . $testimonie->lastname }}">
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
                        <i class="fas fa-quote-left fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">
                            @if(($searchKey ?? '') !== '' || ($available ?? '') !== '')
                                No se encontraron resultados
                            @else
                                No hay testimonios
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if(($searchKey ?? '') !== '' || ($available ?? '') !== '')
                                No hay testimonios que coincidan con los filtros aplicados.
                            @else
                                Crea el primer testimonio de la plataforma.
                            @endif
                        </p>
                        @if(($searchKey ?? '') !== '' || ($available ?? '') !== '')
                            <a href="{{ route('manager.testimonies') }}" class="btn btn-outline-secondary">
                                Ver todos
                            </a>
                        @else
                            @can('testimonies.create')
                            <a href="{{ route('manager.testimonies.create') }}" class="btn btn-primary">
                                Nuevo testimonio
                            </a>
                            @endcan
                        @endif
                    </div>
                @endif
            </div>

            @if($testimonies->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        Mostrando {{ $testimonies->firstItem() }}–{{ $testimonies->lastItem() }} de {{ $testimonies->total() }} testimonios
                    </span>
                    {{ $testimonies->appends(request()->input())->links() }}
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
                    <a href="{{ route('manager.testimonies') }}" class="btn btn-secondary w-100">
                        Limpiar filtros
                    </a>
                </div>
            </div>
        </div>
    </div>

    @include('managers.includes.delete')

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'testimonio(s)',
        'bulkActions' => [
            ['value' => 'publish', 'label' => 'Publicar'],
            ['value' => 'hide', 'label' => 'Ocultar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/settings/testimonies/index.js') }}"></script>
@endpush
