<div class="card">

            {{-- Header --}}
            

            {{-- Stats --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Total</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format(array_sum($stats)) }}</h4>
                                <p class="text-muted">Campañas</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Borradores</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['draft'] ?? 0) }}</h4>
                                <p class="text-muted">En edición</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Enviadas</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['sent'] ?? 0) }}</h4>
                                <p class="text-muted">Completadas</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Con error</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['failed'] ?? 0) }}</h4>
                                <p class="text-muted">Fallidas</p>
                            </div>
                        </div>
                    </div>
                    @if(($stats['sending'] ?? 0) > 0)
                    <div class="col-6 col-md">
                        <div class="card bg-warning-subtle h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Enviando</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['sending']) }}</h4>
                                <p class="text-muted">En progreso</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Filtros --}}
            <div class="card-body border-bottom">
                @php
                    $statusLabels  = ['draft' => 'Borrador', 'sending' => 'Enviando', 'sent' => 'Enviada', 'failed' => 'Fallida'];

                    $filterChips = [];
                    if (($status ?? '') !== '') {
                        $filterChips[] = [
                            'label'     => 'Estado: ' . ($statusLabels[$status] ?? $status),
                            'clear_url' => route('manager.newsletter.campaigns.index', array_filter(['search' => $search ?? ''])),
                        ];
                    }
                @endphp
                <form method="GET" action="{{ route('manager.newsletter.campaigns.index') }}" id="searchForm">
                    <input type="hidden" name="status" id="filterStatus" value="{{ $status ?? '' }}">
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
                    @php $popoverBody = trim(ob_get_clean()); @endphp

                    @include('managers.includes.filter-toolbar', [
                        'searchName' => 'search',
                        'searchValue' => $search ?? '',
                        'searchPlaceholder' => 'Buscar por nombre o asunto...',
                        'popoverBody' => $popoverBody,
                        'filterChips' => $filterChips,
                    ])
                </form>
            </div>

            {{-- Contenido: vacío o tabla --}}
            @if($campaigns->isEmpty())
                <div class="card-body">
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-paper-plane fa-3x mb-3 d-block opacity-25"></i>
                        <h5 class="fw-bold mb-2">
                            @if($search || $status) No se encontraron campañas @else No hay campañas @endif
                        </h5>
                        <p class="mb-4">
                            @if($search || $status)
                                Ninguna campaña coincide con los filtros aplicados.
                            @else
                                Crea tu primera campaña de newsletter para empezar a enviar.
                            @endif
                        </p>
                        @if($search || $status)
                            <a href="{{ route('manager.newsletter.campaigns.index') }}" class="btn btn-outline-secondary">Ver todas</a>
                        @else
                            <a href="{{ route('manager.newsletter.campaigns.create') }}" class="btn btn-primary">Crear primera campaña</a>
                        @endif
                    </div>
                </div>
            @else
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="col-checkbox">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>Nombre</th>
                                    <th>Asunto</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Enviados</th>
                                    <th>Fecha</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($campaigns as $campaign)
                                <tr data-id="{{ $campaign->id }}">
                                    <td>
                                        <input type="checkbox" class="form-check-input bulk-checkbox"
                                               value="{{ $campaign->id }}">
                                    </td>
                                    <td class="fw-medium">{{ $campaign->name }}</td>
                                    <td class="text-muted small text-truncate" title="{{ $campaign->subject }}">
                                        {{ $campaign->subject }}
                                    </td>
                                    <td class="text-center">
                                        @switch($campaign->status)
                                            @case('draft')
                                                <span class="badge bg-secondary-subtle text-secondary">Borrador</span>
                                                @break
                                            @case('sending')
                                                <span class="badge bg-warning-subtle text-warning">
                                                    <i class="fas fa-circle-notch fa-spin me-1"></i>Enviando
                                                </span>
                                                @break
                                            @case('sent')
                                                <span class="badge bg-success-subtle text-success">Enviada</span>
                                                @break
                                            @case('failed')
                                                <span class="badge bg-danger-subtle text-danger">Fallida</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td class="text-center small">
                                        @if($campaign->isSent())
                                            <span class="text-success fw-medium">{{ number_format($campaign->sent_count) }}</span>
                                            @if($campaign->failed_count > 0)
                                                <span class="text-muted"> / </span>
                                                <span class="text-danger">{{ number_format($campaign->failed_count) }} err.</span>
                                            @endif
                                            <br><p class="text-muted">de {{ number_format($campaign->recipients_count) }}</p>
                                        @elseif($campaign->isSending())
                                            <span class="text-warning">{{ number_format($campaign->recipients_count) }} dest.</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">
                                        @if($campaign->sent_at)
                                            Enviada {{ $campaign->sent_at->diffForHumans() }}
                                        @elseif($campaign->started_at)
                                            Iniciada {{ $campaign->started_at->diffForHumans() }}
                                        @else
                                            Creada {{ $campaign->created_at->diffForHumans() }}
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('manager.newsletter.campaigns.preview', $campaign) }}" target="_blank">
                                                        Vista previa
                                                    </a>
                                                </li>
                                                @if($campaign->isDraft())
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('manager.newsletter.campaigns.edit', $campaign) }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <button class="dropdown-item btn-send" type="button"
                                                            data-id="{{ $campaign->id }}"
                                                            data-name="{{ $campaign->name }}"
                                                            data-url="{{ route('manager.newsletter.campaigns.send', $campaign) }}">
                                                            Enviar campaña
                                                        </button>
                                                    </li>
                                                @endif
                                                @if($campaign->isFailed())
                                                    <li>
                                                        <button class="dropdown-item btn-retry" type="button"
                                                            data-url="{{ route('manager.newsletter.campaigns.retry', $campaign) }}">
                                                            Reintentar
                                                        </button>
                                                    </li>
                                                @endif
                                                @if(! $campaign->isSending())
                                                    <li>
                                                        <button class="dropdown-item btn-duplicate" type="button"
                                                            data-url="{{ route('manager.newsletter.campaigns.duplicate', $campaign) }}">
                                                            Duplicar
                                                        </button>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <button class="dropdown-item btn-delete" type="button"
                                                            data-id="{{ $campaign->id }}"
                                                            data-url="{{ route('manager.newsletter.campaigns.destroy', $campaign) }}">
                                                            Eliminar
                                                        </button>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @include('managers.includes.pagination-footer', [
                'paginator' => $campaigns,
                'itemLabel' => 'campañas',
            ])

        </div>
