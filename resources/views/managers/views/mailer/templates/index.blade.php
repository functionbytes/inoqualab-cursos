@extends('layouts.managers')

@section('title', 'Plantillas de email')

@section('content')

    <div class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}" data-flash-success-title="Éxito"
         data-flash-error="{{ session('error') }}" data-flash-error-title="Error">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Plantillas de email</h5>
                        <p class="small mb-0 text-muted">Gestiona plantillas de email para documentos, órdenes y notificaciones</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('mailers.components.index') }}" class="btn btn-outline-secondary">
                            Ver componentes
                        </a>
                        <a href="{{ route('mailers.templates.create') }}" class="btn btn-primary">
                            Nuevo template
                        </a>
                    </div>
                </div>
            </div>

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
                <form method="GET" action="{{ route('mailers.templates.index') }}">
                    <div class="d-flex flex-column flex-lg-row gap-3 align-items-stretch">
                        <div class="flex-fill">
                            <div class="input-group h-100">
                                <span class="input-group-text bg-white border-end-1">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="search" name="search" class="form-control border-start-0 ps-0"
                                       placeholder="Buscar por nombre, key o descripción..."
                                       value="{{ $search }}">
                            </div>
                        </div>
                        @if(!empty($modules))
                            <div class="flex-shrink-0 filter-select-lg">
                                <select name="module" class="form-select select2 h-100">
                                    <option value="">Todos los módulos</option>
                                    @foreach($modules as $mod)
                                        <option value="{{ $mod }}" {{ $module === $mod ? 'selected' : '' }}>{{ ucfirst($mod) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="d-flex gap-2 flex-shrink-0">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i>
                            </button>
                            @if($search || $module)
                                <a href="{{ route('mailers.templates.index') }}" class="btn btn-outline-secondary" title="Limpiar filtros">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </div>
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

            {{-- Pagination --}}
            @if($templates->hasPages())
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Mostrando <strong>{{ $templates->firstItem() }}</strong> a <strong>{{ $templates->lastItem() }}</strong>
                            de <strong>{{ $templates->total() }}</strong> plantillas
                        </div>
                        {{ $templates->links() }}
                    </div>
                </div>
            @endif

        </div>
    </div>

    <div id="bulk-config" class="d-none"
         data-bulk-url="{{ route('mailers.templates.bulk-action') }}"
         data-send-test-base-url="{{ url('settings/mailers/templates') }}"></div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'plantilla(s)',
        'bulkActions' => [
            ['value' => 'activate', 'label' => 'Activar'],
            ['value' => 'deactivate', 'label' => 'Desactivar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    {{-- Send Test Modal --}}
    <div class="modal fade" id="modalSendTest" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" id="sendTestForm" action="">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Enviar email de prueba</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info border-0">
                            <strong>Plantilla:</strong> <span id="sendTestTemplateName"></span><br>
                            <strong>Asunto:</strong> <span id="sendTestTemplateSubject"></span>
                        </div>
                        <div class="mb-3">
                            <label for="send_test_email" class="form-label fw-semibold">Email de destino</label>
                            <input type="email" class="form-control" id="send_test_email"
                                   name="test_email" placeholder="tu@email.com" required>
                            <small class="form-text text-muted">Se enviará un email con variables de ejemplo</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary w-100 mb-1" id="sendTestSubmitBtn">
                            Enviar ahora
                        </button>
                        <button type="button" class="btn btn-light w-100" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete modal --}}
    <div class="modal fade" id="delete-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4 position-relative">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                <div class="mb-3 mt-2">
                    <i class="fas fa-triangle-exclamation text-warning fs-icon-lg"></i>
                </div>
                <h5 class="fw-bold mb-2">¿Estás seguro de eliminar esto?</h5>
                <p class="text-muted mb-4">Esta acción no se puede deshacer. Todos los datos relacionados pueden eliminarse.</p>
                <form id="delete-form" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-primary w-100 mb-2">Confirmar eliminación</button>
                    <button type="button" class="btn btn-dark w-100" data-bs-dismiss="modal">Cancelar</button>
                </form>
            </div>
        </div>
    </div>
    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/mailer/templates/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/flash-toastr.js') }}"></script>
<script src="{{ asset('managers/js/views/mailer/templates/index.js') }}"></script>
@endpush
