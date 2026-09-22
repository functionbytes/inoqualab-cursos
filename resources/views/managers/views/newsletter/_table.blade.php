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

                    @php ob_start(); @endphp
                <div class="filter-popover-field">
                    <div class="filter-popover-label">Estado</div>
                    <div class="filter-popover-options">
                        <label class="filter-popover-option">
                            <input type="radio" data-filter-name="popover_Status" value="" {{ ($status ?? '') === '' ? 'checked' : '' }}>
                            <span class="filter-popover-dot"></span>
                            <span>Todos</span>
                        </label>
                        @foreach($statusLabels as $value => $label)
                            <label class="filter-popover-option">
                                <input type="radio" data-filter-name="popover_Status" value="{{ $value }}" {{ ($status ?? '') === $value ? 'checked' : '' }}>
                                <span class="filter-popover-dot"></span>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="filter-popover-field">
                    <div class="filter-popover-label">Origen</div>
                    <div class="filter-popover-options">
                        <label class="filter-popover-option">
                            <input type="radio" data-filter-name="popover_Source" value="" {{ ($source ?? '') === '' ? 'checked' : '' }}>
                            <span class="filter-popover-dot"></span>
                            <span>Todos los orígenes</span>
                        </label>
                        @foreach($sourceLabels as $value => $label)
                            <label class="filter-popover-option">
                                <input type="radio" data-filter-name="popover_Source" value="{{ $value }}" {{ ($source ?? '') === $value ? 'checked' : '' }}>
                                <span class="filter-popover-dot"></span>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                    @php $popoverBody = trim(ob_get_clean()); @endphp

                    @include('managers.includes.filter-toolbar', [
                        'searchName' => 'search',
                        'searchValue' => $search ?? '',
                        'searchPlaceholder' => 'Buscar por email o nombre...',
                        'popoverBody' => $popoverBody,
                        'filterChips' => $filterChips,
                    ])
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

            @include('managers.includes.pagination-footer', [
                'paginator' => $subscribers,
                'itemLabel' => 'suscriptores',
            ])

        </div>
