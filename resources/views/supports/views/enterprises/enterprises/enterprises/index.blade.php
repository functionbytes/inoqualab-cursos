@extends('layouts.managers')

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('support.enterprises.create') }}" class="btn btn-primary">
                            Nueva empresa
                        </a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('supports.includes.card', [
        'title' => 'Empresas',
        'description' => 'Gestiona las empresas registradas en la plataforma',
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Header --}}
            

            {{-- Stats --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Total</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['total']) }}</h4>
                                <span class="text-muted">Empresas registradas</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Publicas</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['public']) }}</h4>
                                <span class="text-muted">Visibles</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Ocultas</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['hidden']) }}</h4>
                                <span class="text-muted">No visibles</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Search + Filtros --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('support.enterprises') }}" id="searchForm"
                      data-bulk-url="{{ route('support.enterprises.bulk-action') }}">

                    <input type="hidden" name="available" id="filterAvailable" value="{{ $available ?? '' }}">

                    <div class="d-flex gap-2 align-items-center">
                        <div class="flex-fill">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="search" name="search" class="form-control border-start-0 ps-0"
                                       placeholder="Buscar por nit, correo o titulo..."
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
                @if($enterprises->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="col-checkbox">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>Titulo</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($enterprises as $enterprise)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox"
                                                   value="{{ $enterprise->id }}">
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ Str::words(Str::upper(Str::lower($enterprise->title)), 8, '...') }}</div>
                                        </td>
                                        <td class="text-center">
                                            @if($enterprise->available)
                                                <span class="badge bg-success-subtle text-success">Publico</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Oculto</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ date('d/m/Y', strtotime($enterprise->updated_at)) }}</span>
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
                                                           href="{{ route('support.enterprises.navegation', $enterprise->slack) }}">
                                                            Dashboard
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item"
                                                           href="{{ route('support.enterprises.edit', $enterprise->slack) }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item confirm-delete"
                                                           href="#"
                                                           data-href="{{ route('support.enterprises.destroy', $enterprise->slack) }}">
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
                        <i class="fas fa-building fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">
                            @if(($searchKey ?? '') || ($available ?? '') !== '')
                                No se encontraron resultados
                            @else
                                No hay empresas
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if(($searchKey ?? '') || ($available ?? '') !== '')
                                No hay empresas que coincidan con los filtros aplicados.
                            @else
                                Crea la primera empresa de la plataforma.
                            @endif
                        </p>
                        @if(($searchKey ?? '') || ($available ?? '') !== '')
                            <a href="{{ route('support.enterprises') }}" class="btn btn-outline-secondary">
                                Ver todas
                            </a>
                        @else
                            <a href="{{ route('support.enterprises.create') }}" class="btn btn-primary">
                                Nueva empresa
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            @if($enterprises->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        Mostrando {{ $enterprises->firstItem() }}–{{ $enterprises->lastItem() }} de {{ $enterprises->total() }} empresas
                    </span>
                    {{ $enterprises->appends(request()->input())->links() }}
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
                    <a href="{{ route('support.enterprises') }}" class="btn btn-secondary w-100">
                        Limpiar filtros
                    </a>
                </div>
            </div>
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'empresa(s)',
        'bulkActions' => [
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('supports/css/enterprises/enterprises/enterprises/index.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('supports/js/enterprises/enterprises/enterprises/index.js') }}"></script>
@endpush
