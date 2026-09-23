<div class="card">

            {{-- Header --}}
            

            {{-- Info --}}
            <div class="card-body border-bottom">
                <div class="alert alert-info border-0 mb-0">
                    <div class="d-flex align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-info-circle fs-5 me-3 mt-1"></i>
                            <div>
                                <h6 class="fw-bold mb-1">¿Necesitas editar el Header o Footer?</h6>
                                <p class="mb-0 small">Los componentes como header, footer y otros elementos reutilizables se gestionan por separado. Edítalos una vez y se aplicarán automáticamente a todas las plantillas.</p>
                            </div>
                        </div>
                        <a href="{{ route('mailers.components.index') }}" class="btn btn-info btn-sm flex-shrink-0">
                            Ver
                        </a>
                    </div>
                </div>
            </div>

            {{-- Search --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('mailers.templates.index') }}" id="searchForm">
                    @php
                        $filterChips = [];
                        if (($module ?? '') !== '') {
                            $filterChips[] = [
                                'label' => 'Módulo: ' . ucfirst($module),
                                'clear_url' => url()->current() . '?' . http_build_query(request()->except('module')),
                            ];
                        }
                    @endphp
                    <input type="hidden" name="module" id="filterModule" value="{{ $module ?? '' }}">

                    @if(!empty($modules))
                        @php ob_start(); @endphp
                        <div class="filter-popover-field">
                            <div class="filter-popover-label">Módulo</div>
                            <div class="filter-popover-options">
                                <label class="filter-popover-option">
                                    <input type="radio" data-filter-name="popover_Module" value="" {{ ($module ?? '') === '' ? 'checked' : '' }}>
                                    <span class="filter-popover-dot"></span>
                                    <span>Todos</span>
                                </label>
                                @foreach($modules as $mod)
                                    <label class="filter-popover-option">
                                        <input type="radio" data-filter-name="popover_Module" value="{{ $mod }}" {{ $module === $mod ? 'checked' : '' }}>
                                        <span class="filter-popover-dot"></span>
                                        <span>{{ ucfirst($mod) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @php $popoverBody = trim(ob_get_clean()); @endphp
                    @endif

                    @include('managers.includes.filter-toolbar', [
                        'searchName' => 'search',
                        'searchValue' => $search ?? '',
                        'searchPlaceholder' => 'Buscar por nombre, key o descripción...',
                        'popoverBody' => $popoverBody ?? null,
                        'filterChips' => $filterChips,
                    ])
                </form>
            </div>

            {{-- Table --}}
            <div class="card-body">
                @if($templates->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="3%"><input type="checkbox" id="select-all" class="form-check-input"></th>
                                    <th>Nombre</th>
                                    <th>Clave (Key)</th>
                                    <th>Modulo</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($templates as $template)
                                    <tr>
                                        <td><input type="checkbox" class="form-check-input bulk-checkbox" value="{{ $template->id }}"></td>
                                        <td>
                                            <strong class="d-block">{{ $template->name }}</strong>
                                            @if($template->description)
                                                <p class="text-muted">{{ Str::limit($template->description, 50) }}</p>
                                            @endif
                                        </td>
                                        <td><code class="text-muted">{{ $template->key }}</code></td>
                                        <td>
                                            <span class="badge bg-light text-info">{{ ucfirst($template->module) }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($template->is_enabled)
                                                <span class="badge bg-success text-white">Activo</span>
                                            @else
                                                <span class="badge bg-danger text-white">Inactivo</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <a href="#" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('mailers.templates.edit', $template->uid) }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('mailers.templates.preview', $template->uid) }}" target="_blank">
                                                            Vista previa
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <button type="button" class="dropdown-item btn-send-test"
                                                                data-template-uid="{{ $template->uid }}"
                                                                data-template-name="{{ $template->name }}"
                                                                data-template-subject="{{ $template->subject }}">
                                                            Enviar prueba
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <form method="POST" action="{{ route('mailers.templates.toggle-status', $template->uid) }}">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item">
                                                                {{ $template->is_enabled ? 'Desactivar' : 'Activar' }}
                                                            </button>
                                                        </form>
                                                    </li>
                                                    @if(!$template->is_protected)
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item delete-btn" href="javascript:void(0)"
                                                               data-bs-toggle="modal" data-bs-target="#delete-modal"
                                                               data-url="{{ route('mailers.templates.destroy', $template->uid) }}"
                                                               data-title="Eliminar: {{ $template->name }}"
                                                               data-method="DELETE">
                                                                Eliminar
                                                            </a>
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
                @else
                    <div class="text-center py-5">
                        <div class="d-flex flex-column align-items-center">
                            <div class="round-48 rounded-circle bg-light text-muted mb-3 d-flex align-items-center justify-content-center">
                                <i class="fas fa-envelope-open fs-7"></i>
                            </div>
                            <h6 class="mb-1">
                                @if($search || $module)
                                    No se encontraron plantillas
                                @else
                                    No hay plantillas configuradas
                                @endif
                            </h6>
                            <p class="text-muted mb-3">
                                @if($search || $module)
                                    No hay resultados para los criterios de búsqueda
                                @else
                                    Crea la primera plantilla de email
                                @endif
                            </p>
                            @if(!$search && !$module)
                                <a href="{{ route('mailers.templates.create') }}" class="btn btn-sm btn-primary">
                                    Nuevo template
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            @include('managers.includes.pagination-footer', [
                'paginator' => $templates,
                'itemLabel' => 'plantillas',
            ])

        </div>
