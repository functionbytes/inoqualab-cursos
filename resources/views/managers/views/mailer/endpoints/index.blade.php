@extends('layouts.managers')

@section('title', 'Email endpoints')

@section('content')

<div class="widget-content searchable-container list">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul><button class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card">
        {{-- Header --}}
        <div class="card-header p-4 border-bottom border-light">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1 fw-bold">Endpoints configurados</h5>
                    <p class="small mb-0 text-muted">Gestiona los endpoints para enviar correos desde aplicaciones externas</p>
                </div>
                <a href="{{ route('mailers.endpoints.create') }}" class="btn btn-primary">
                    Crear endpoint
                </a>
            </div>
        </div>

        {{-- Stats --}}
        <div class="card-body border-bottom">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="card bg-light h-100">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-2">Total endpoints</h6>
                            <h4 class="mb-1 fw-bold">{{ $stats['total'] }}</h4>
                            <p class="text-muted">Configurados</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light h-100">
                        <div class="card-body">
                            <h6 class="card-title mb-2">Activos</h6>
                            <h4 class="mb-1 fw-bold">{{ $stats['active'] }}</h4>
                            <p class="text-muted">En funcionamiento</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light h-100">
                        <div class="card-body">
                            <h6 class="card-title text-warning mb-2">Inactivos</h6>
                            <h4 class="mb-1 fw-bold">{{ $stats['inactive'] }}</h4>
                            <p class="text-muted">Desactivados</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light h-100">
                        <div class="card-body">
                            <h6 class="card-title text-info mb-2">Total requests</h6>
                            <h4 class="mb-1 fw-bold">{{ number_format($stats['total_requests']) }}</h4>
                            <p class="text-muted">Enviados</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Search & Filter --}}
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('mailers.endpoints.index') }}">
                <div class="row g-2">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                            <input type="search" name="search" class="form-control"
                                   placeholder="Buscar por nombre o slug..."
                                   value="{{ $search ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="source" class="form-select select2">
                            <option value="">Todas las fuentes</option>
                            @foreach($sources as $src)
                                <option value="{{ $src }}" @if(($source ?? '') === $src) selected @endif>{{ $src }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select select2">
                            <option value="">Todos los estados</option>
                            <option value="active" @if(($status ?? '') === 'active') selected @endif>Activos</option>
                            <option value="inactive" @if(($status ?? '') === 'inactive') selected @endif>Inactivos</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Buscar</button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="card-body">
            @if($endpoints->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Fuente</th>
                                <th>Tipo</th>
                                <th class="text-center">Requests</th>
                                <th class="text-center">Éxito</th>
                                <th class="text-center">Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($endpoints as $endpoint)
                                @php
                                    $total = $endpoint->requests_count;
                                    $successRate = $total > 0 ? round(($endpoint->success_logs_count / $total) * 100, 1) : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <span class="fw-bold d-block">{{ $endpoint->name }}</span>
                                        <p class="text-muted">{{ $endpoint->slug }}</p>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-primary rounded-pill py-1 px-2">{{ $endpoint->source }}</span>
                                    </td>
                                    <td>
                                        <code class="bg-light px-2 py-1 rounded">{{ $endpoint->type }}</code>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary-subtle text-dark rounded-pill py-1 px-2">{{ $total }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($total > 0)
                                            <div class="d-flex align-items-center justify-content-center gap-2">
                                                <small class="fw-semibold">{{ $successRate }}%</small>
                                                <div class="progress" style="width:60px;height:6px;">
                                                    <div class="progress-bar bg-success endpoint-progress-bar" role="progressbar"
                                                         data-width="{{ $successRate }}" style="width:0"
                                                         aria-valuenow="{{ $successRate }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($endpoint->is_active)
                                            <span class="badge rounded-pill py-1 px-2" style="background:#36c76c;color:#fff">Activo</span>
                                        @else
                                            <span class="badge rounded-pill py-1 px-2" style="background:#fa4c3c;color:#fff">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <a href="#" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Acciones">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('mailers.endpoints.edit', $endpoint) }}">
                                                        Editar
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('mailers.endpoints.logs', $endpoint) }}">
                                                        Ver logs
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <button type="button" class="dropdown-item"
                                                            onclick="document.getElementById('delete-form').action='{{ route('mailers.endpoints.destroy', $endpoint) }}'"
                                                            data-bs-toggle="modal" data-bs-target="#delete-modal">
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
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x mb-3 text-muted opacity-50"></i>
                    <h6 class="mb-1">No hay endpoints configurados</h6>
                    <p class="text-muted mb-3">
                        @if(request('search'))
                            No se encontraron resultados para "{{ request('search') }}"
                        @else
                            Crea tu primer endpoint para gestionar emails desde aplicaciones externas
                        @endif
                    </p>
                    @if(!request('search'))
                        <a href="{{ route('mailers.endpoints.create') }}" class="btn btn-primary">
                            Crear endpoint
                        </a>
                    @endif
                </div>
            @endif
        </div>

        {{-- Pagination --}}
        @if($endpoints->hasPages())
            <div class="card-footer bg-white border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Mostrando {{ $endpoints->firstItem() }}-{{ $endpoints->lastItem() }} de {{ $endpoints->total() }} resultados</span>
                    {{ $endpoints->links() }}
                </div>
            </div>
        @endif
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
                     ('DELETE')
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

    $('.endpoint-progress-bar').each(function() {
        var width = Math.max(0, Math.min(100, parseFloat($(this).data('width')) || 0));
        $(this).css('width', width + '%');
    });
});
</script>
@endpush
