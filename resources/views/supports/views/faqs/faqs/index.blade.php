@extends('layouts.managers')

@section('content')

    <div class="widget-content searchable-container list" id="faqs-list"
         data-bulk-url="{{ route('support.faqs.bulk-action') }}">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Preguntas frecuentes</h5>
                        <p class="mb-0 text-muted">Gestiona las preguntas frecuentes del portal</p>
                    </div>
                    <div class="ms-auto">
                        <a href="{{ route('support.faqs.create') }}" class="btn btn-primary">
                            Nueva pregunta
                        </a>
                    </div>
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
                                <span class="text-muted">Preguntas configuradas</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Publicadas</h6>
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
                <form method="GET" action="{{ route('support.faqs') }}" id="searchForm">

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
                @if($faqs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="col-checkbox">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>Título</th>
                                    <th>Categoría</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($faqs as $faq)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox"
                                                   value="{{ $faq->id }}">
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ Str::words($faq->title, 8, '...') }}</div>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $faq->categorie->title ?? '—' }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($faq->available == 1)
                                                <span class="badge bg-success-subtle text-success">Publicada</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Oculta</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ $faq->updated_at->format('d/m/Y') }}</span>
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
                                                           href="{{ route('support.faqs.edit', $faq->slack) }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item confirm-delete" href="#"
                                                           data-href="{{ route('support.faqs.destroy', $faq->slack) }}">
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
                        <i class="fas fa-circle-question fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">
                            @if($searchKey || ($available ?? '') !== '')
                                No se encontraron resultados
                            @else
                                No hay preguntas frecuentes
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if($searchKey || ($available ?? '') !== '')
                                No hay preguntas que coincidan con los filtros aplicados.
                            @else
                                Crea la primera pregunta frecuente.
                            @endif
                        </p>
                        @if($searchKey || ($available ?? '') !== '')
                            <a href="{{ route('support.faqs') }}" class="btn btn-outline-secondary">
                                Ver todas
                            </a>
                        @else
                            <a href="{{ route('support.faqs.create') }}" class="btn btn-primary">
                                Nueva pregunta
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            @if($faqs->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        Mostrando {{ $faqs->firstItem() }}–{{ $faqs->lastItem() }} de {{ $faqs->total() }} preguntas
                    </span>
                    {{ $faqs->appends(request()->input())->links() }}
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
                    <a href="{{ route('support.faqs') }}" class="btn btn-secondary w-100">
                        Limpiar filtros
                    </a>
                </div>
            </div>
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'pregunta(s)',
        'bulkActions' => [
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('supports/css/views/faqs/faqs/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('supports/js/views/faqs/faqs/index.js') }}"></script>
@endpush
