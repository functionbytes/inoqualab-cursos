{{--
    Partial AJAX: filtros + tabla + paginacion de componentes de email.

    Se incluye normalmente desde index.blade.php (dentro de #ajax-table-root)
    para el render inicial, y el controller devuelve ESTE mismo partial (sin
    layout) cuando la peticion es AJAX ($request->ajax()) al buscar, filtrar
    o paginar. Ver public/managers/js/ajax-table.js.
--}}
<div class="card">
    {{-- Info --}}
    <div class="card-body border-bottom">
        <div class="alert alert-info border-0 mb-0">
            <div class="d-flex align-items-start">
                <i class="fas fa-info-circle fs-5 me-3 mt-1"></i>
                <div>
                    <h6 class="fw-bold mb-2">¿Qué son los componentes de email?</h6>
                    <p class="mb-0">Los componentes son partes reutilizables de HTML que se aplican automáticamente a todas las plantillas de email. Por ejemplo, el <strong>header</strong> y <strong>footer</strong> se insertan en cada email que envías.</p>
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

    {{-- Filters --}}
    <div class="card-body border-bottom">
        <form method="GET" action="{{ route('mailers.components.index') }}" id="searchForm">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-7">
                    <label for="search" class="form-label fw-semibold">Búsqueda</label>
                    <input type="text" id="search" name="search" class="form-control"
                           placeholder="Buscar por alias, nombre o código..."
                           value="{{ $search ?? '' }}">
                </div>

                @if(!empty($types))
                    <div class="col-12 col-sm-6 col-md-3">
                        <label for="type" class="form-label fw-semibold">Tipo</label>
                        <select class="form-select select2" id="type" name="type">
                            <option value="">Todos los tipos</option>
                            @foreach($types as $t)
                                <option value="{{ $t }}" @if(($type ?? '') === $t) selected @endif>{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-12 col-sm-6 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        Buscar
                    </button>
                    @if(!empty($search) || !empty($type))
                        <a href="{{ route('mailers.components.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- Table --}}
    @if($components->count() > 0)
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="col-checkbox">
                            <input type="checkbox" class="form-check-input" id="select-all">
                        </th>
                        <th width="30%">Componente</th>
                        <th width="16%">Código</th>
                        <th width="12%">Tipo</th>
                        <th width="15%">Estado</th>
                        <th width="15%" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($components as $component)
                        @php
                            $isCritical = in_array($component->alias, ['email_template_header', 'email_template_footer', 'email_template_wrapper']);
                        @endphp
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input bulk-checkbox"
                                       value="{{ $component->id }}">
                            </td>
                            <td>
                                <strong class="d-block">{{ $component->subject ?? $component->alias }}</strong>
                                <small class="text-muted d-block">{{ $component->alias }}</small>
                                @if($isCritical)
                                    <span class="badge bg-light text-warning mt-1">
                                        <i class="fas fa-star"></i> Sistema
                                    </span>
                                @endif
                            </td>
                            <td>
                                <code class="text-muted">{{ $component->code }}</code>
                            </td>
                            <td>
                                @if($component->type === 'partial')
                                    <span class="badge bg-light text-info">Parcial</span>
                                @elseif($component->type === 'layout')
                                    <span class="badge bg-light text-success">Layout</span>
                                @else
                                    <span class="badge bg-light text-secondary">{{ ucfirst($component->type) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($isCritical)
                                    <span class="badge bg-light text-warning">Protegido</span>
                                @else
                                    <span class="badge bg-light text-secondary">Personalizado</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <a href="#" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Acciones">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('mailers.components.edit', $component->uid) }}">
                                                Editar
                                            </a>
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ route('mailers.components.duplicate', $component->uid) }}">
                                                @csrf
                                                <button type="submit" class="dropdown-item">Duplicar</button>
                                            </form>
                                        </li>
                                        @if(!$isCritical)
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button type="button" class="dropdown-item js-delete-component"
                                                        data-delete-url="{{ route('mailers.components.destroy', $component->uid) }}"
                                                        data-bs-toggle="modal" data-bs-target="#delete-modal">
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

        @include('managers.includes.pagination-footer', [
            'paginator' => $components,
            'itemLabel' => 'componentes',
        ])
    </div>
    @else
    <div class="card-body">
        <div class="text-center py-5">
            <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-inbox', 48) !!}</div>
            <h5 class="fw-bold mb-2">No hay componentes</h5>
            <p class="text-muted mb-4">
                @if(!empty($search) || !empty($type))
                    No se encontraron resultados con los filtros aplicados.
                @else
                    Comienza creando tu primer componente reutilizable.
                @endif
            </p>
            @if(!empty($search) || !empty($type))
                <a href="{{ route('mailers.components.index') }}" class="btn btn-secondary">Ver todos</a>
            @else
                <a href="{{ route('mailers.components.create') }}" class="btn btn-primary">Crear ahora</a>
            @endif
        </div>
    </div>
    @endif

    {{-- System Components Info --}}
    <div class="card-body border-top">
        <h5 class="fw-bold mb-1">Componentes del sistema</h5>
        <p class="text-muted mb-4">Estos componentes son esenciales y se aplican automáticamente a todas las plantillas.</p>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3 icon-circle-48">
                                <i class="fas fa-arrow-up text-primary"></i>
                            </div>
                            <h6 class="fw-bold mb-0">Header</h6>
                        </div>
                        <p class="text-muted mb-2 small">Se inserta al inicio de cada email. Incluye logo, estilos CSS y apertura de HTML.</p>
                        <code class="small text-primary">email_template_header</code>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3 icon-circle-48">
                                <i class="fas fa-arrow-down text-success"></i>
                            </div>
                            <h6 class="fw-bold mb-0">Footer</h6>
                        </div>
                        <p class="text-muted mb-2 small">Se inserta al final de cada email. Incluye información de la empresa y cierre de HTML.</p>
                        <code class="small text-success">email_template_footer</code>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3 icon-circle-48">
                                <i class="fas fa-layer-group text-info"></i>
                            </div>
                            <h6 class="fw-bold mb-0">Wrapper</h6>
                        </div>
                        <p class="text-muted mb-2 small">Layout completo que combina header + contenido + footer.</p>
                        <code class="small text-info">email_template_wrapper</code>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
