@extends('layouts.managers')

@section('title', 'Newsletter')

@section('content')


    <div class="widget-content searchable-container list" id="newsletter-page"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-bulk-url="{{ route('manager.newsletter.bulk-action') }}"
         data-store-url="{{ route('manager.newsletter.store') }}"
         data-import-url="{{ route('manager.newsletter.import') }}">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Suscriptores del newsletter</h5>
                        <p class="small mb-0 text-muted">Gestiona los suscriptores y exporta la lista</p>
                    </div>
                    <div class="ms-auto d-flex gap-2">
                        <div class="btn-group">
                            <button type="button" class="btn bg-primary-subtle text-primary dropdown-toggle"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Acciones
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="{{ route('manager.newsletter.campaigns.index') }}">
                                    Campañas
                                </a>
                                <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#add-modal">
                                    Añadir suscriptor
                                </button>
                                <div class="dropdown-divider"></div>
                                <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#import-modal">
                                    Importar CSV
                                </button>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('manager.newsletter.export') }}">Exportar CSV</a>
                                @if($search || ($status !== null && $status !== '') || ($source !== null && $source !== ''))
                                    <a class="dropdown-item" href="{{ route('manager.newsletter.export', array_filter(['search' => $search, 'source' => $source, 'status' => $status])) }}">Exportar CSV (filtrado)</a>
                                @endif
                            </div>
                        </div>
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
                                <p class="text-muted">Registrados</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Activos</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['active']) }}</h4>
                                <p class="text-muted">Suscritos</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Dados de baja</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['inactive']) }}</h4>
                                <p class="text-muted">Desuscritos</p>
                            </div>
                        </div>
                    </div>
                    @if($stats['pending'] > 0)
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Pendientes</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['pending']) }}</h4>
                                <p class="text-muted">Sin confirmar</p>
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Plataforma</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['platform']) }}</h4>
                                <p class="text-muted">Usuarios registrados</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Este mes</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['month']) }}</h4>
                                <p class="text-muted">Nuevos en {{ now()->translatedFormat('M Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filtros --}}
            <div class="card-body border-bottom">
                @php
                    $activeFilters = (int)(($status ?? '') !== '') + (int)(($source ?? '') !== '');
                    $statusLabels = ['active' => 'Suscritos', 'inactive' => 'Desuscritos', 'pending' => 'Pendiente confirmación'];
                    $sourceLabels = ['form' => 'Formulario', 'registration' => 'Registro', 'manual' => 'Manual', 'import' => 'Importación'];

                    $filterChips = [];
                    if (($status ?? '') !== '') {
                        $filterChips[] = [
                            'label'     => 'Estado: ' . ($statusLabels[$status] ?? $status),
                            'clear_url' => route('manager.newsletter.index', array_filter(['search' => $search ?? '', 'source' => $source ?? ''])),
                        ];
                    }
                    if (($source ?? '') !== '') {
                        $filterChips[] = [
                            'label'     => 'Origen: ' . ($sourceLabels[$source] ?? $source),
                            'clear_url' => route('manager.newsletter.index', array_filter(['search' => $search ?? '', 'status' => $status ?? ''])),
                        ];
                    }
                @endphp
                <form method="GET" action="{{ route('manager.newsletter.index') }}" id="searchForm">
                    <input type="hidden" name="status" id="filterStatus" value="{{ $status ?? '' }}">
                    <input type="hidden" name="source" id="filterSource" value="{{ $source ?? '' }}">

                    <div class="d-flex gap-2 align-items-center">
                        <div class="flex-fill">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="search" name="search" class="form-control border-start-0 ps-0"
                                       placeholder="Buscar por email o nombre..."
                                       value="{{ $search ?? '' }}">
                            </div>
                        </div>

                        <button type="button" class="btn btn-dark flex-shrink-0 position-relative"
                                data-bs-toggle="modal" data-bs-target="#filters-modal"
                                title="Filtros avanzados">
                            <i class="fas fa-sliders"></i>
                            @if($activeFilters > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary filter-badge-sm">{{ $activeFilters }}</span>
                            @endif
                        </button>

                        @if($activeFilters > 0)
                            <a href="{{ route('manager.newsletter.index', array_filter(['search' => $search ?? ''])) }}"
                               class="btn btn-dark flex-shrink-0" title="Limpiar filtros">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif

                        <button type="submit" class="btn btn-primary flex-shrink-0">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>

                    @include('managers.includes.filter-chips', ['chips' => $filterChips])
                </form>
            </div>

            {{-- Tabla --}}
            @if($subscribers->count() > 0)
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="col-checkbox">
                                    <input type="checkbox" class="form-check-input" id="select-all">
                                </th>
                                <th>Email</th>
                                <th>Nombre</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Origen</th>
                                <th class="text-center">Suscrito el</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subscribers as $subscriber)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input bulk-checkbox"
                                               value="{{ $subscriber->id }}">
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $subscriber->email }}</span>
                                    </td>
                                    <td>{{ $subscriber->name ?? '—' }}</td>
                                    <td class="text-center">
                                        @if($subscriber->is_active)
                                            <span class="badge bg-success-subtle text-success">Suscrito</span>
                                        @elseif($subscriber->confirmation_token)
                                            <span class="badge bg-warning-subtle text-warning">Pendiente</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">Desuscrito</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($subscriber->source === 'registration')
                                            <span class="badge bg-primary-subtle text-primary">Registro</span>
                                        @elseif($subscriber->source === 'manual')
                                            <span class="badge bg-info-subtle text-info">Manual</span>
                                        @elseif($subscriber->source === 'import')
                                            <span class="badge bg-warning-subtle text-warning">Importación</span>
                                        @else
                                            <span class="badge bg-light text-dark border">Formulario</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <p class="text-muted">
                                            {{ ($subscriber->subscribed_at ?? $subscriber->created_at)?->format('d/m/Y H:i') }}
                                        </p>
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <a href="#" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-vertical"></i>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                @if($subscriber->is_active)
                                                    <li>
                                                        <button type="button" class="dropdown-item btn-unsubscribe"
                                                                data-id="{{ $subscriber->id }}"
                                                                data-email="{{ $subscriber->email }}">
                                                            Desuscribir
                                                        </button>
                                                    </li>
                                                @elseif($subscriber->confirmation_token)
                                                    <li>
                                                        <button type="button" class="dropdown-item btn-resend"
                                                                data-url="{{ route('manager.newsletter.resend-confirmation', $subscriber) }}"
                                                                data-email="{{ $subscriber->email }}">
                                                            Reenviar confirmación
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button type="button" class="dropdown-item btn-resubscribe"
                                                                data-id="{{ $subscriber->id }}"
                                                                data-email="{{ $subscriber->email }}">
                                                            Activar directamente
                                                        </button>
                                                    </li>
                                                @else
                                                    <li>
                                                        <button type="button" class="dropdown-item btn-resubscribe"
                                                                data-id="{{ $subscriber->id }}"
                                                                data-email="{{ $subscriber->email }}">
                                                            Reactivar
                                                        </button>
                                                    </li>
                                                @endif
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <button type="button" class="dropdown-item btn-delete"
                                                            data-url="{{ route('manager.newsletter.destroy', $subscriber) }}"
                                                            data-title="Eliminar: {{ $subscriber->email }}">
                                                        Eliminar
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="card-body">
                <div class="text-center py-5">
                    <i class="fas fa-paper-plane fa-3x mb-3 text-muted opacity-50"></i>
                    <h5 class="fw-bold mb-2">No hay suscriptores</h5>
                    <p class="text-muted mb-4">
                        @if($search || ($status !== null && $status !== '') || ($source !== null && $source !== ''))
                            No se encontraron resultados con los filtros aplicados.
                        @else
                            Los suscriptores aparecerán aquí cuando alguien se registre o complete el formulario.
                        @endif
                    </p>
                    @if($search || ($status !== null && $status !== '') || ($source !== null && $source !== ''))
                        <a href="{{ route('manager.newsletter.index') }}" class="btn btn-outline-secondary">
                            Ver todos
                        </a>
                    @endif
                </div>
            </div>
            @endif

            @if($subscribers->hasPages())
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Mostrando {{ $subscribers->firstItem() }}–{{ $subscribers->lastItem() }} de {{ $subscribers->total() }} suscriptores
                        </div>
                        {{ $subscribers->appends(request()->input())->links() }}
                    </div>
                </div>
            @endif

        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'suscriptor(es)',
        'bulkActions' => [
            ['value' => 'resubscribe', 'label' => 'Reactivar'],
            ['value' => 'unsubscribe', 'label' => 'Desuscribir'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    @include('managers.includes.delete')

    {{-- Modal: añadir suscriptor --}}
    <div class="modal fade" id="add-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Añadir suscriptor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add-email" class="form-label fw-semibold">Correo electrónico <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="add-email" placeholder="correo@ejemplo.com">
                        <div id="add-email-error" class="invalid-feedback"></div>
                    </div>
                    <div class="mb-0">
                        <label for="add-name" class="form-label fw-semibold">Nombre <span class="text-muted fw-normal">(opcional)</span></label>
                        <input type="text" class="form-control" id="add-name" placeholder="Nombre del suscriptor">
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button id="btn-add-confirm" type="button" class="btn btn-primary w-100 mb-2">Añadir</button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: importar CSV --}}
    <div class="modal fade" id="import-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Importar suscriptores desde CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info border-0 py-2 mb-3">
                        <small>
                            El archivo debe tener una columna de <strong>email</strong> y opcionalmente una de <strong>nombre</strong>.
                            La primera fila puede ser una cabecera — se detecta automáticamente.
                        </small>
                    </div>
                    <div class="mb-0">
                        <label for="import-file" class="form-label fw-semibold">Archivo CSV <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="import-file" accept=".csv,.txt">
                        <small class="text-muted d-block mt-1">Máximo 2 MB. Separador: coma o punto y coma.</small>
                        <div id="import-file-error" class="invalid-feedback d-block"></div>
                    </div>
                    <div id="import-result" class="mt-3 d-none">
                        <div class="card bg-light-secondary border-0">
                            <div class="card-body py-2">
                                <p class="mb-0 small" id="import-result-text"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button id="btn-import-confirm" type="button" class="btn btn-primary w-100 mb-2">Importar</button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: filtros avanzados --}}
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
                        <select id="modalStatus" class="form-select">
                            <option value="">Todos</option>
                            <option value="active" @selected(($status ?? '') === 'active')>Suscritos</option>
                            <option value="inactive" @selected(($status ?? '') === 'inactive')>Desuscritos</option>
                            <option value="pending" @selected(($status ?? '') === 'pending')>Pendiente confirmación</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Origen</label>
                        <select id="modalSource" class="form-select">
                            <option value="">Todos los orígenes</option>
                            <option value="form" @selected(($source ?? '') === 'form')>Formulario</option>
                            <option value="registration" @selected(($source ?? '') === 'registration')>Registro</option>
                            <option value="manual" @selected(($source ?? '') === 'manual')>Manual</option>
                            <option value="import" @selected(($source ?? '') === 'import')>Importación</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer flex-column border-top-0 pt-0">
                    <button type="button" id="applyFiltersBtn" class="btn btn-primary w-100 mb-2">Aplicar filtros</button>
                    <a href="{{ route('manager.newsletter.index') }}" class="btn btn-outline-secondary w-100">Limpiar filtros</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal desuscribir / reactivar individual --}}
    <div class="modal fade" id="action-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4 position-relative">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    <div class="mb-3 mt-2">
                        <i class="fas fa-triangle-exclamation text-warning action-modal-icon"></i>
                    </div>
                    <h5 class="fw-bold mb-2" id="action-modal-title"></h5>
                    <p class="text-muted mb-4" id="action-modal-body"></p>
                    <button id="btn-action-confirm" type="button" class="btn btn-primary w-100 mb-2">Confirmar</button>
                    <button type="button" class="btn btn-dark w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/newsletter/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/newsletter/index.js') }}"></script>
@endpush
