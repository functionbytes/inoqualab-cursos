@extends('layouts.managers')

@section('title', 'Variables de email')

@section('content')

<div class="widget-content searchable-container list">

    <div class="card">
        {{-- Header --}}
        <div class="card-header p-4 border-bottom border-light">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1 fw-bold">Variables de email</h5>
                    <p class="small mb-0 text-muted">Gestiona variables dinámicas que se sustituyen automáticamente en plantillas y componentes</p>
                </div>
                <a href="{{ route('mailers.variables.create') }}" class="btn btn-primary">
                    Nueva variable
                </a>
            </div>
        </div>

        {{-- Info --}}
        <div class="card-body border-bottom">
            <div class="alert alert-info border-0 mb-0">
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

        {{-- Filters --}}
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('mailers.variables.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-4">
                        <label for="search" class="form-label fw-semibold">Búsqueda</label>
                        <input type="text" id="search" name="search" class="form-control"
                               placeholder="Buscar por clave o nombre..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <label for="module" class="form-label fw-semibold">Módulo</label>
                        <select class="form-select select2" id="module" name="module">
                            <option value="">Todos los módulos</option>
                            <option value="core" @selected(request('module') === 'core')>Core</option>
                            <option value="documents" @selected(request('module') === 'documents')>Documentos</option>
                            <option value="orders" @selected(request('module') === 'orders')>Pedidos</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <label for="category" class="form-label fw-semibold">Categoría</label>
                        <select class="form-select select2" id="category" name="category">
                            <option value="">Todas las categorías</option>
                            <option value="system" @selected(request('category') === 'system')>Sistema</option>
                            <option value="customer" @selected(request('category') === 'customer')>Cliente</option>
                            <option value="order" @selected(request('category') === 'order')>Pedido</option>
                            <option value="document" @selected(request('category') === 'document')>Documento</option>
                            <option value="general" @selected(request('category') === 'general')>General</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="fas fa-search me-2"></i>Buscar
                        </button>
                        @if(request('search') || request('module') || request('category'))
                            <a href="{{ route('mailers.variables.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        {{-- Table --}}
        @if($variables->count() > 0)
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="20%">Clave</th>
                            <th width="20%">Nombre</th>
                            <th width="12%">Categoría</th>
                            <th width="12%">Módulo</th>
                            <th width="10%">Tipo</th>
                            <th width="8%">Estado</th>
                            <th width="18%" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($variables as $variable)
                            <tr>
                                <td>
                                    <code class="text-primary d-block">{{ $variable->key }}</code>
                                    @if($variable->is_system)
                                        <span class="badge bg-light text-warning mt-1">
                                            <i class="fas fa-lock"></i> Sistema
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <strong class="d-block">{{ $variable->name }}</strong>
                                    @if($variable->description)
                                        <small class="text-muted d-block">{{ Str::limit($variable->description, 40) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @switch($variable->category)
                                        @case('system')<span class="badge bg-light text-primary">Sistema</span>@break
                                        @case('customer')<span class="badge bg-light text-info">Cliente</span>@break
                                        @case('order')<span class="badge bg-light text-dark">Pedido</span>@break
                                        @case('document')<span class="badge bg-light text-warning">Documento</span>@break
                                        @default<span class="badge bg-light text-secondary">General</span>
                                    @endswitch
                                </td>
                                <td>
                                    @switch($variable->module)
                                        @case('documents')<span class="badge bg-light text-info">Documentos</span>@break
                                        @case('orders')<span class="badge bg-light text-dark">Pedidos</span>@break
                                        @default<span class="badge bg-light text-primary">Core</span>
                                    @endswitch
                                </td>
                                <td>
                                    @if($variable->is_system)
                                        <span class="badge bg-light text-warning">Protegido</span>
                                    @else
                                        <span class="badge bg-light text-secondary">Personalizado</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input toggle-status" type="checkbox"
                                               @checked($variable->is_enabled)
                                               data-url="{{ route('mailers.variables.toggle-status', $variable) }}"
                                               title="{{ $variable->is_enabled ? 'Activa' : 'Inactiva' }}">
                                    </div>
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
                                                    <button type="button" class="dropdown-item"
                                                            onclick="document.getElementById('delete-form').action='{{ route('mailers.variables.destroy', $variable) }}'"
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
                <i class="fas fa-inbox fa-3x mb-3 text-muted opacity-50"></i>
                <h5 class="fw-bold mb-2">No hay variables</h5>
                <p class="text-muted mb-4">
                    @if(request('search') || request('module') || request('category'))
                        No se encontraron resultados con los filtros aplicados.
                    @else
                        Comienza creando tu primera variable de email para usar en plantillas.
                    @endif
                </p>
                @if(request('search') || request('module') || request('category'))
                    <a href="{{ route('mailers.variables.index') }}" class="btn btn-secondary">Ver todas</a>
                @else
                    <a href="{{ route('mailers.variables.create') }}" class="btn btn-primary">Crear ahora</a>
                @endif
            </div>
        </div>
        @endif

        {{-- Pagination --}}
        @if($variables->hasPages())
            <div class="card-footer bg-white border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Mostrando {{ $variables->firstItem() }} - {{ $variables->lastItem() }} de {{ $variables->total() }} variables</span>
                    {{ $variables->links() }}
                </div>
            </div>
        @endif

        {{-- Categories Info --}}
        <div class="card-body border-top">
            <h5 class="fw-bold mb-1">Categorías de variables</h5>
            <p class="text-muted mb-4">Las variables se organizan en categorías según el tipo de dato que representan.</p>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;">
                                    <i class="fas fa-cog text-primary"></i>
                                </div>
                                <h6 class="fw-bold mb-0">Sistema</h6>
                            </div>
                            <p class="text-muted mb-2 small">Variables globales del sistema como nombre de la empresa, URL, información de contacto.</p>
                            <code class="small text-primary">&#123;&#123;company_name&#125;&#125;</code>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;">
                                    <i class="fas fa-user text-info"></i>
                                </div>
                                <h6 class="fw-bold mb-0">Cliente</h6>
                            </div>
                            <p class="text-muted mb-2 small">Datos del cliente destinatario del email como nombre, email, dirección.</p>
                            <code class="small text-info">&#123;&#123;customer_name&#125;&#125;</code>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px;">
                                    <i class="fas fa-file-alt text-warning"></i>
                                </div>
                                <h6 class="fw-bold mb-0">Documento/Pedido</h6>
                            </div>
                            <p class="text-muted mb-2 small">Información específica de documentos, pedidos y transacciones.</p>
                            <code class="small text-warning">&#123;&#123;order_number&#125;&#125;</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

{{-- Delete modal --}}
<div class="modal fade" id="delete-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4 position-relative">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                <div class="mb-3 mt-2">
                    <i class="fas fa-triangle-exclamation text-warning" style="font-size:3.5rem;"></i>
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

@push('scripts')
<script>
$(document).ready(function() {
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({ allowClear: false, width: '100%' });
    }

    $('.toggle-status').on('change', function() {
        const $toggle = $(this);
        $.ajax({
            url: $toggle.data('url'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(data) {
                if (!data.success) $toggle.prop('checked', !$toggle.prop('checked'));
            },
            error: function() {
                $toggle.prop('checked', !$toggle.prop('checked'));
                toastr.error('Error al cambiar el estado');
            }
        });
    });
});
</script>
@endpush
