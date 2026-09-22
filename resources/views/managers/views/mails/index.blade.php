@extends('layouts.managers')

@section('title', 'Correos entrantes')

@section('page_header')
    @php ob_start(); @endphp
    <div class="btn-group">
        <button type="button" class="btn btn-outline-secondary dropdown-toggle"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Acciones
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            <button type="button" class="dropdown-item" id="aliases-btn">
                Alias faltantes (últimos 30 días)
            </button>
            <a class="dropdown-item" href="{{ route('manager.mails') }}">
                Configuración de correos
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="#" id="export-btn">
                Exportar CSV
            </a>
        </div>
    </div>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Correos entrantes',
        'description' => 'Gestiona y procesa los correos recibidos de empresas',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div class="widget-content searchable-container list" id="mails-index"
         data-config='@php $__jsonInline1 = [
            "authUserId" => auth()->id(),
            "status" => $status,
            "chartData" => $chartData->values(),
            "routes" => [
                "enterprises" => route("manager.mails.enterprises"),
                "courses" => route("manager.mails.courses"),
                "bulkAction" => route("manager.mails.bulk-action"),
                "missingAliases" => route("manager.mails.missing-aliases"),
                "createAlias" => route("manager.mails.create-alias"),
                "export" => route("manager.mails.export"),
                "index" => route("manager.mails.index"),
                "confirm" => route("manager.mails.confirm", ":slack"),
                "show" => route("manager.mails.show", ":slack"),
                "preview" => route("manager.mails.preview", ":slack"),
                "discard" => route("manager.mails.discard", ":slack"),
                "assign" => route("manager.mails.assign", ":slack"),
            ],
         ]; @endphp@json($__jsonInline1)'>

        <div class="card">

            {{-- Header --}}
            

            {{-- Stats --}}
            <div class="card-body border-bottom">
                @php
                    $pendingDelta   = ($stats['pending_today'] ?? 0) - ($stats['pending_yesterday'] ?? 0);
                    $processedDelta = ($stats['processed_today'] ?? 0) - ($stats['processed_yesterday'] ?? 0);
                @endphp
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <a href="{{ route('manager.mails.index', ['status' => 'pending_review']) }}" class="text-decoration-none">
                            <div class="card bg-light-secondary h-100">
                                <div class="card-body">
                                    <h6 class="card-title mb-2">Pendientes hoy</h6>
                                    <h4 class="mb-1 fw-bold text-warning">{{ $stats['pending_today'] }}</h4>
                                    <span class="{{ $pendingDelta > 0 ? 'text-danger' : ($pendingDelta < 0 ? 'text-success' : 'text-muted') }}">
                                        <i class="fas {{ $pendingDelta > 0 ? 'fa-arrow-up' : ($pendingDelta < 0 ? 'fa-arrow-down' : 'fa-minus') }} me-1"></i>{{ $pendingDelta > 0 ? '+' . $pendingDelta : $pendingDelta }} vs ayer
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="{{ route('manager.mails.index', ['status' => 'processed']) }}" class="text-decoration-none">
                            <div class="card bg-light-secondary h-100">
                                <div class="card-body">
                                    <h6 class="card-title mb-2">Procesados hoy</h6>
                                    <h4 class="mb-1 fw-bold text-success">{{ $stats['processed_today'] }}</h4>
                                    <span class="{{ $processedDelta > 0 ? 'text-success' : ($processedDelta < 0 ? 'text-danger' : 'text-muted') }}">
                                        <i class="fas {{ $processedDelta > 0 ? 'fa-arrow-up' : ($processedDelta < 0 ? 'fa-arrow-down' : 'fa-minus') }} me-1"></i>{{ $processedDelta > 0 ? '+' . $processedDelta : $processedDelta }} vs ayer
                                    </span>
                                    @if(!is_null($stats['avg_minutes_today']))
                                        <br><span class="text-muted"><i class="fas fa-clock me-1"></i>{{ $stats['avg_minutes_today'] }} min promedio</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="{{ route('manager.mails.index', ['status' => 'failed']) }}" class="text-decoration-none">
                            <div class="card bg-light-secondary h-100">
                                <div class="card-body">
                                    <h6 class="card-title mb-2">Fallidos esta semana</h6>
                                    <h4 class="mb-1 fw-bold text-danger">{{ $stats['failed_week'] }}</h4>
                                    <span class="text-muted">Errores de procesamiento</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="{{ route('manager.mails.index') }}" class="text-decoration-none">
                            <div class="card bg-light-secondary h-100">
                                <div class="card-body">
                                    <h6 class="card-title mb-2">Sin resolver</h6>
                                    <h4 class="mb-1 fw-bold text-primary">{{ $stats['unresolved'] }}</h4>
                                    <span class="text-muted">Requieren atención</span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Gráfica de actividad --}}
            <div class="card-body border-bottom">
                <h6 class="fw-bold mb-1">Actividad — últimos 7 días</h6>
                <p class="text-muted mb-3">Correos recibidos, procesados y fallidos por día</p>
                <div id="mails-activity-chart" class="mails-activity-chart"></div>
            </div>

            {{-- Estado rápido --}}
            <div class="card-body border-bottom py-2">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    @php
                        $badgeMap = [
                            'pending_review' => ['label' => 'Pendientes', 'class' => 'bg-warning text-white'],
                            'processed'      => ['label' => 'Procesados', 'class' => 'bg-success text-white'],
                            'failed'         => ['label' => 'Fallidos',   'class' => 'bg-danger text-white'],
                            'ignored'        => ['label' => 'Ignorados',  'class' => 'bg-secondary text-white'],
                        ];
                    @endphp
                    <span class="text-muted me-1">Totales:</span>
                    @foreach($badgeMap as $key => $cfg)
                        @if($counts->has($key))
                            <span class="badge {{ $cfg['class'] }} rounded-3 py-1 px-2">
                                {{ $cfg['label'] }} {{ $counts->get($key) }}
                            </span>
                        @endif
                    @endforeach
                    @if($counts->isEmpty())
                        <span class="text-muted">Sin registros</span>
                    @endif
                </div>
            </div>

            {{-- Filtros --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('manager.mails.index') }}" id="searchForm">

                    <input type="hidden" name="status"        id="filterStatus"     value="{{ request('status', '') }}">
                    <input type="hidden" name="enterprise_id" id="filterEnterprise"  value="{{ request('enterprise_id', '') }}">
                    <input type="hidden" name="confidence"    id="filterConfidence"  value="{{ request('confidence', '') }}">
                    <input type="hidden" name="assigned_to"   id="filterAssignedTo"  value="{{ request('assigned_to', '') }}">
                    <input type="hidden" id="date_from" name="date_from" value="{{ request('date_from', '') }}">
                    <input type="hidden" id="date_to"   name="date_to"   value="{{ request('date_to', '') }}">

                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <div class="flex-fill">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted">
                                    {!! \App\Html\IconHelper::render('search') !!}
                                </span>
                                <input type="search" name="search" class="form-control border-start-0 ps-0"
                                       placeholder="Buscar por remitente o asunto..."
                                       value="{{ request('search') }}">
                            </div>
                        </div>

                        <div class="flex-shrink-0">
                            <input type="text" class="form-control daterange mails-daterange-input" id="daterange"
                                   placeholder="Rango de fechas" autocomplete="off"
                                   value="{{ (request('date_from') && request('date_to')) ? request('date_from') . ' - ' . request('date_to') : '' }}">
                        </div>

                        @php
                            $activeFilters = (int)(request('status', '') !== '')
                                + (int)(request('enterprise_id', '') !== '')
                                + (int)(request('confidence', '') !== '')
                                + (int)(request('assigned_to', '') !== '');
                        @endphp
                        <button type="button" class="btn btn-outline-secondary btn-icon flex-shrink-0" title="Filtros"
                                data-bs-toggle="modal" data-bs-target="#filters-modal">
                            {!! \App\Html\IconHelper::render('sliders') !!}
                            @if($activeFilters > 0)
                                <span class="badge bg-primary ms-1">{{ $activeFilters }}</span>
                            @endif
                        </button>

                        <button type="submit" class="btn btn-primary btn-icon flex-shrink-0" title="Buscar" aria-label="Buscar">
                            {!! \App\Html\IconHelper::render('search') !!}
                        </button>

                        @if(request('search') || request('status') || request('enterprise_id') || request('confidence') || request('assigned_to') || request('date_from'))
                            <a href="{{ route('manager.mails.index') }}" class="btn btn-outline-secondary flex-shrink-0"
                               title="Limpiar filtros">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Tabla --}}
            <div class="card-body">
                @php
                    $baseParams = array_filter(request()->only(['search','status','enterprise_id','date_from','date_to','confidence','per_page']));
                    $sortLink = function (string $col) use ($baseParams, $sort, $direction): string {
                        $newDir = ($sort === $col && $direction === 'desc') ? 'asc' : 'desc';
                        return route('manager.mails.index') . '?' . http_build_query(array_merge($baseParams, ['sort' => $col, 'direction' => $newDir]));
                    };
                    $sortIcon = function (string $col) use ($sort, $direction): string {
                        if ($sort !== $col) return '<i class="fas fa-sort ms-1 text-muted"></i>';
                        return '<i class="fas fa-sort-' . ($direction === 'asc' ? 'up' : 'down') . ' ms-1 text-primary"></i>';
                    };
                @endphp
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-nowrap">
                        <thead class="table-light">
                            <tr>
                                <th class="mails-col-checkbox">
                                    <input type="checkbox" class="form-check-input" id="select-all-checkbox">
                                </th>
                                <th>Remitente</th>
                                <th>Asunto</th>
                                <th>Empresa</th>
                                <th>
                                    <a href="{{ $sortLink('confidence_score') }}" class="text-dark text-decoration-none">
                                        Confianza {!! $sortIcon('confidence_score') !!}
                                    </a>
                                </th>
                                <th>Estado</th>
                                <th>
                                    <a href="{{ $sortLink('received_at') }}" class="text-dark text-decoration-none">
                                        Recibido {!! $sortIcon('received_at') !!}
                                    </a>
                                </th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="mails-tbody">
                            @include('managers.views.mails._rows')
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                    <div class="d-flex align-items-center gap-2">
                        @if($mails->total() > 0)
                            <span class="text-muted">Mostrando {{ $mails->firstItem() }}–{{ $mails->lastItem() }} de {{ $mails->total() }}</span>
                        @endif
                        <select id="per-page-select" class="form-select form-select-sm w-auto">
                            @foreach([15, 25, 50] as $pp)
                                <option value="{{ $pp }}" {{ $perPage == $pp ? 'selected' : '' }}>{{ $pp }} por página</option>
                            @endforeach
                        </select>
                    </div>
                    @if($mails->hasPages())
                        <nav>{{ $mails->appends(request()->input())->links() }}</nav>
                    @endif
                </div>
            </div>

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
                        <select id="modalStatus" class="form-select">
                            <option value="">Todos los estados</option>
                            <option value="pending_review" {{ request('status') === 'pending_review' ? 'selected' : '' }}>Pendientes</option>
                            <option value="processed"      {{ request('status') === 'processed'      ? 'selected' : '' }}>Procesados</option>
                            <option value="failed"         {{ request('status') === 'failed'         ? 'selected' : '' }}>Fallidos</option>
                            <option value="ignored"        {{ request('status') === 'ignored'        ? 'selected' : '' }}>Ignorados</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Empresa</label>
                        <select id="modalEnterprise" class="form-select w-100">
                            @if($selectedEnterprise)
                                <option value="{{ $selectedEnterprise->id }}" selected>{{ $selectedEnterprise->title }}</option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Confianza</label>
                        <select id="modalConfidence" class="form-select">
                            <option value="">Cualquier confianza</option>
                            <option value="high"   {{ request('confidence') === 'high'   ? 'selected' : '' }}>Alta (≥ 90%)</option>
                            <option value="medium" {{ request('confidence') === 'medium' ? 'selected' : '' }}>Media (50–89%)</option>
                            <option value="low"    {{ request('confidence') === 'low'    ? 'selected' : '' }}>Baja (&lt; 50%)</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Revisor</label>
                        <select id="modalAssignedTo" class="form-select">
                            <option value=""          {{ request('assigned_to') === ''           ? 'selected' : '' }}>Todos los revisores</option>
                            <option value="me"        {{ request('assigned_to') === 'me'         ? 'selected' : '' }}>Mis correos</option>
                            <option value="unassigned" {{ request('assigned_to') === 'unassigned' ? 'selected' : '' }}>Sin asignar</option>
                            @if($reviewers->isNotEmpty())
                                <optgroup label="Por revisor">
                                    @foreach($reviewers as $reviewer)
                                        <option value="{{ $reviewer->id }}"
                                            {{ (string) request('assigned_to') === (string) $reviewer->id ? 'selected' : '' }}>
                                            {{ trim($reviewer->firstname . ' ' . $reviewer->lastname) }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button type="button" id="applyFiltersBtn" class="btn btn-primary w-100 mb-2">
                        Aplicar filtros
                    </button>
                    <a href="{{ route('manager.mails.index') }}" class="btn btn-secondary w-100">
                        Limpiar filtros
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal alias faltantes --}}
    <div id="aliases-modal" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Alias faltantes — últimos 30 días</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="aliases-modal-body">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin text-primary fs-3"></i>
                        <p class="text-muted mt-2">Analizando correos...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal confirmación rápida --}}
    <div id="quick-confirm-modal" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Confirmación rápida</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="text-success fs-1 mb-3"><i class="fas fa-circle-check"></i></div>
                    <p class="fw-semibold mb-1">¿Confirmar este correo?</p>
                    <p class="text-muted fw-semibold mb-2" id="qc-enterprise-name"></p>
                    <p class="text-muted mb-0">Se usarán los alias existentes para mapear los cursos automáticamente.</p>
                </div>
                <div class="modal-footer flex-column">
                    <button type="button" id="quick-confirm-btn" class="btn btn-success w-100 mb-2">
                        Confirmar y crear orden
                    </button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal preview --}}
    <div id="preview-modal" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Vista previa</h5>
                        <span class="text-muted" id="preview-from"></span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0" id="preview-modal-body">
                    <div class="text-center py-5">
                        <i class="fas fa-spinner fa-spin text-primary fs-3"></i>
                        <p class="text-muted mt-2">Cargando correo...</p>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <a href="#" id="preview-open-link" class="btn btn-primary w-100 mb-2">
                        Abrir revisión completa
                    </a>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal descartar --}}
    <div id="discard-modal" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Descartar correo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="text-warning fs-1 mb-3"><i class="fas fa-triangle-exclamation"></i></div>
                    <p class="fw-semibold mb-1">¿Descartar este correo?</p>
                    <p class="text-muted mb-0">Será marcado como ignorado y no generará orden.</p>
                </div>
                <div class="modal-footer flex-column">
                    <button type="button" id="discard-confirm-btn" class="btn btn-danger w-100 mb-2">Confirmar</button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'correo(s)',
        'bulkActions' => [
            ['value' => 'reparse', 'label' => 'Re-analizar'],
            ['value' => 'discard', 'label' => 'Descartar'],
        ],
    ])

    @include('managers.includes.delete')

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/mails/index.css') }}">
@endpush

@push('scripts')
<script src="{{ url('managers/libs/daterangepicker/moment.min.js') }}"></script>
<script src="{{ url('managers/libs/daterangepicker/daterangepicker.js') }}"></script>
<script src="{{ asset('managers/js/views/mails/index.js') }}"></script>
@endpush
