<div class="card">
        {{-- Header --}}
        

        {{-- Info --}}
        <div class="card-body border-bottom">
            <div class="alert alert-light border mb-0">
                <div class="d-flex align-items-start">
                    <i class="fas fa-info-circle fs-5 me-3 mt-1"></i>
                    <div>
                        <h6 class="fw-bold mb-2">¿Qué son las variables de email?</h6>
                        <p class="mb-0">Las variables son marcadores de posición como <code>&#123;&#123;customer_name&#125;&#125;</code> que se reemplazan automáticamente con datos reales cuando se envía un email.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="card-body border-bottom">
                <div class="alert alert-success alert-dismissible fade show mb-0">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
            </div>
        @endif
        @if(session('error'))
            <div class="card-body border-bottom">
                <div class="alert alert-danger alert-dismissible fade show mb-0">{{ session('error') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
            </div>
        @endif
        @if($errors->any())
            <div class="card-body border-bottom">
                <div class="alert alert-danger alert-dismissible fade show mb-0"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul><button class="btn-close" data-bs-dismiss="alert"></button></div>
            </div>
        @endif

        {{-- Search + Filtros --}}
        <div class="card-body border-bottom">
            @php
                $statuses = ['1' => 'Activa', '0' => 'Inactiva'];
                $filterChips = [];
                if (($module ?? '') !== '') {
                    $filterChips[] = [
                        'label' => 'Módulo: ' . ($modules[$module] ?? $module),
                        'clear_url' => url()->current() . '?' . http_build_query(request()->except('module')),
                    ];
                }
                if (($category ?? '') !== '') {
                    $filterChips[] = [
                        'label' => 'Categoría: ' . ($categories[$category] ?? $category),
                        'clear_url' => url()->current() . '?' . http_build_query(request()->except('category')),
                    ];
                }
                if (($status ?? '') !== '') {
                    $filterChips[] = [
                        'label' => 'Estado: ' . ($statuses[$status] ?? $status),
                        'clear_url' => url()->current() . '?' . http_build_query(request()->except('status')),
                    ];
                }
            @endphp
            <form method="GET" action="{{ route('mailers.variables.index') }}" id="searchForm">

                <input type="hidden" name="module" id="filterModule" value="{{ $module ?? '' }}">
                <input type="hidden" name="category" id="filterCategory" value="{{ $category ?? '' }}">
                <input type="hidden" name="status" id="filterStatus" value="{{ $status ?? '' }}">

                @php ob_start(); @endphp
            <div class="filter-popover-field">
                <div class="filter-popover-label">Módulo</div>
                <div class="filter-popover-options">
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_module" value="" {{ ($module ?? '') === '' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Todos</span>
                    </label>
                    @foreach($modules as $value => $label)
                        <label class="filter-popover-option">
                            <input type="radio" data-filter-name="popover_module" value="{{ $value }}" {{ (string) ($module ?? '') === (string) $value ? 'checked' : '' }}>
                            <span class="filter-popover-dot"></span>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="filter-popover-field">
                <div class="filter-popover-label">Categoría</div>
                <div class="filter-popover-options">
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_category" value="" {{ ($category ?? '') === '' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Todas</span>
                    </label>
                    @foreach($categories as $value => $label)
                        <label class="filter-popover-option">
                            <input type="radio" data-filter-name="popover_category" value="{{ $value }}" {{ (string) ($category ?? '') === (string) $value ? 'checked' : '' }}>
                            <span class="filter-popover-dot"></span>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="filter-popover-field">
                <div class="filter-popover-label">Estado</div>
                <div class="filter-popover-options">
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_status" value="" {{ ($status ?? '') === '' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Todos</span>
                    </label>
                    @foreach($statuses as $value => $label)
                        <label class="filter-popover-option">
                            <input type="radio" data-filter-name="popover_status" value="{{ $value }}" {{ (string) ($status ?? '') === (string) $value ? 'checked' : '' }}>
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
                    'searchPlaceholder' => 'Buscar por clave, nombre o descripción...',
                    'popoverBody' => $popoverBody,
                    'filterChips' => $filterChips,
                ])
            </form>
        </div>

        {{-- Table --}}
        @if($variables->count() > 0)
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="col-checkbox">
                                <input type="checkbox" class="form-check-input" id="select-all">
                            </th>
                            <th width="20%">Clave</th>
                            <th width="20%">Nombre</th>
                            <th width="12%">Categoría</th>
                            <th width="12%">Módulo</th>
                            <th width="10%">Tipo</th>
                            <th width="18%" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($variables as $variable)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input bulk-checkbox"
                                           value="{{ $variable->id }}">
                                </td>
                                <td>
                                    <span class="d-flex align-items-center gap-2">
                                        <code class="text-muted">{{ $variable->key }}</code>
                                        @if($variable->is_system)
                                            <span class="badge bg-secondary-subtle badge-icon-only" title="Sistema" aria-label="Sistema">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <strong class="d-block">{{ $variable->name }}</strong>
                                    @if($variable->description)
                                        <small class="text-muted d-block">{{ Str::limit($variable->description, 40) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle">{{ \App\Models\Mailer\MailerVariable::CATEGORIES[$variable->category] ?? $variable->category }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle">{{ \App\Models\Mailer\MailerVariable::MODULES[$variable->module] ?? $variable->module }}</span>
                                </td>
                                <td>
                                    @if($variable->is_system)
                                        <span class="badge bg-secondary-subtle">Protegido</span>
                                    @else
                                        <span class="badge bg-secondary-subtle">Personalizado</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <a href="#" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Acciones">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('mailers.variables.edit', $variable) }}">
                                                    Editar variable
                                                </a>
                                            </li>
                                            @if(!$variable->is_system)
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <button type="button" class="dropdown-item js-delete-variable"
                                                            data-delete-url="{{ route('mailers.variables.destroy', $variable) }}"
                                                            data-bs-toggle="modal" data-bs-target="#delete-modal">
                                                        Eliminar variable
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
        @else
        <div class="card-body">
            <div class="text-center py-5">
                <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-inbox', 48) !!}</div>
                <h5 class="fw-bold mb-2">No hay variables</h5>
                <p class="text-muted mb-4">
                    @if(!empty($search) || !empty($module) || !empty($category) || ($status ?? '') !== '')
                        No se encontraron resultados con los filtros aplicados.
                    @else
                        Comienza creando tu primera variable de email para usar en plantillas.
                    @endif
                </p>
                @if(!empty($search) || !empty($module) || !empty($category) || ($status ?? '') !== '')
                    <a href="{{ route('mailers.variables.index') }}" class="btn btn-secondary">Ver todas</a>
                @else
                    <a href="{{ route('mailers.variables.create') }}" class="btn btn-primary">Crear ahora</a>
                @endif
            </div>
        </div>
        @endif

        @include('managers.includes.pagination-footer', [
            'paginator' => $variables,
            'itemLabel' => 'variables',
        ])

        {{-- Categories Info --}}
        <div class="card-body border-top">
            <h5 class="fw-bold mb-1">Categorías de variables</h5>
            <p class="text-muted mb-4">Las variables se organizan en categorías según el tipo de dato que representan.</p>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card bg-light h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-3 icon-circle-48">
                                    <i class="fas fa-cog text-white"></i>
                                </div>
                                <h6 class="fw-bold mb-0">Sistema</h6>
                            </div>
                            <p class="text-muted mb-2 small">Variables globales del sistema como nombre de la empresa, URL, información de contacto.</p>
                            <code class="small text-muted">&#123;&#123;company_name&#125;&#125;</code>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-3 icon-circle-48">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                                <h6 class="fw-bold mb-0">Cliente</h6>
                            </div>
                            <p class="text-muted mb-2 small">Datos del cliente destinatario del email como nombre, email, dirección.</p>
                            <code class="small text-muted">&#123;&#123;customer_name&#125;&#125;</code>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-3 icon-circle-48">
                                    <i class="fas fa-file-alt text-white"></i>
                                </div>
                                <h6 class="fw-bold mb-0">Documento/Pedido</h6>
                            </div>
                            <p class="text-muted mb-2 small">Información específica de documentos, pedidos y transacciones.</p>
                            <code class="small text-muted">&#123;&#123;order_number&#125;&#125;</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
