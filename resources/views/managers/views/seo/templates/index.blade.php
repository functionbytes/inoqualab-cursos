@extends('layouts.managers')

@section('title', 'Plantillas SEO')

@section('content')


    <div class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}" data-flash-error="{{ session('error') }}">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Plantillas SEO</h5>
                        <p class="mb-0 text-muted">Patrones reutilizables para títulos y descripciones meta</p>
                    </div>
                    <div class="ms-auto">
                        <a href="{{ route('manager.seo.templates.create') }}" class="btn btn-primary">
                            Nueva plantilla
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
                                <span class="text-muted">Plantillas configuradas</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Activas</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['active']) }}</h4>
                                <span class="text-muted">En uso</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Search --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('manager.seo.templates.index') }}" id="searchForm">
                    <div class="d-flex gap-2 align-items-center">
                        <div class="flex-fill">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="search" name="search" class="form-control border-start-0 ps-0"
                                       placeholder="Buscar por nombre..."
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary flex-shrink-0">
                            <i class="fas fa-search"></i>
                        </button>
                        @if(request('search'))
                            <a href="{{ route('manager.seo.templates.index') }}" class="btn btn-outline-secondary flex-shrink-0" title="Limpiar filtros">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="card-body">
                @if($templates->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap">
                            <thead class="table-light">
                                <tr>
                                    <th class="col-checkbox">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>Nombre</th>
                                    <th>Tipo de modelo</th>
                                    <th>Patrón título</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Prioridad</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($templates as $template)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox"
                                                   value="{{ $template->id }}">
                                        </td>
                                        <td>
                                            <span class="fw-semibold">{{ $template->name }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">
                                                {{ $template->model_type ? class_basename($template->model_type) : 'Global' }}
                                            </span>
                                        </td>
                                        <td>
                                            <code>{{ Str::limit($template->title_pattern ?? '—', 50) }}</code>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check form-switch d-flex justify-content-center">
                                                <input type="checkbox"
                                                       class="form-check-input toggle-active"
                                                       id="toggle-{{ $template->id }}"
                                                       data-id="{{ $template->id }}"
                                                       data-url="{{ route('manager.seo.templates.toggle-active', $template) }}"
                                                       {{ $template->is_active ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary-subtle text-secondary">{{ $template->priority }}</span>
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
                                                        <a class="dropdown-item" href="{{ route('manager.seo.templates.edit', $template) }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item apply-btn" href="#"
                                                           data-id="{{ $template->id }}"
                                                           data-preview-url="{{ route('manager.seo.templates.preview', $template) }}"
                                                           data-apply-url="{{ route('manager.seo.templates.bulk-apply', $template) }}"
                                                           data-name="{{ $template->name }}">
                                                            Aplicar a metas
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item btn-delete" href="#"
                                                           data-url="{{ route('manager.seo.templates.destroy', $template) }}"
                                                           data-title="Eliminar: {{ $template->name }}">
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
                        <i class="fas fa-file-code fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">
                            @if(request('search'))
                                No se encontraron resultados
                            @else
                                Sin plantillas
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if(request('search'))
                                No hay plantillas que coincidan con la búsqueda.
                            @else
                                Crea tu primera plantilla SEO para comenzar.
                            @endif
                        </p>
                        @if(request('search'))
                            <a href="{{ route('manager.seo.templates.index') }}" class="btn btn-outline-secondary">
                                Ver todas
                            </a>
                        @else
                            <a href="{{ route('manager.seo.templates.create') }}" class="btn btn-primary">
                                Nueva plantilla
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            @if($templates->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        Mostrando {{ $templates->firstItem() }}–{{ $templates->lastItem() }} de {{ $templates->total() }} plantillas
                    </span>
                    {{ $templates->appends(request()->input())->links() }}
                </div>
            @endif

        </div>
    </div>

    {{-- Apply to metas modal --}}
    <div class="modal fade" id="applyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Aplicar plantilla a metas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center" id="apply-modal-body">
                    <div class="py-3">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Calculando registros afectados...</p>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button type="button" class="btn btn-primary w-100 mb-2" id="confirm-apply-btn" disabled>Aplicar</button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <div id="bulk-config" class="d-none" data-bulk-url="{{ route('manager.seo.templates.bulk-action') }}"></div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'plantilla(s)',
        'bulkActions' => [
            ['value' => 'activate', 'label' => 'Activar'],
            ['value' => 'deactivate', 'label' => 'Desactivar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    @include('managers.includes.delete')

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/shared/tables.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/flash-toastr.js') }}"></script>
<script src="{{ asset('managers/js/views/seo/templates/index.js') }}"></script>
@endpush

