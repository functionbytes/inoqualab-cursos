@extends('layouts.managers')

@section('title', 'Listas de campaña')

@section('content')

    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Listas de campaña</h5>
                        <p class="small mb-0 text-muted">Segmentos de suscriptores. Las listas dinámicas se pueblan solas por eventos.</p>
                    </div>
                    <div class="ms-auto d-flex gap-2">
                        <a href="{{ route('manager.newsletter.campaigns.index') }}" class="btn btn-outline-secondary">
                            Campañas
                        </a>
                        <a href="{{ route('manager.newsletter.lists.create') }}" class="btn btn-primary">
                            + Nueva lista
                        </a>
                    </div>
                </div>
            </div>

            {{-- Tabla --}}
            <div class="card-body">
                @if($lists->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Tipo</th>
                                    <th class="text-center">Suscriptores</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lists as $list)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $list->name }}</div>
                                            @if($list->description)
                                                <div class="small text-muted">{{ Str::limit($list->description, 80) }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($list->trigger === 'manual')
                                                <span class="badge bg-secondary-subtle text-secondary">Manual</span>
                                            @else
                                                <span class="badge bg-info-subtle text-info">Dinámica</span>
                                            @endif
                                        </td>
                                        <td class="text-center fw-semibold">{{ number_format($list->subscribers_count) }}</td>
                                        <td class="text-center">
                                            @if($list->is_active)
                                                <span class="badge bg-success-subtle text-success">Activa</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Inactiva</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0"
                                                        data-bs-toggle="dropdown" data-bs-boundary="viewport">
                                                    <i class="fas fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('manager.newsletter.lists.members', $list->id) }}">
                                                            Ver suscriptores
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('manager.newsletter.lists.edit', $list->id) }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    @if($list->trigger === 'manual')
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item btn-delete" href="#"
                                                               data-url="{{ route('manager.newsletter.lists.destroy', $list->id) }}"
                                                               data-title="Eliminar: {{ $list->name }}">
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
                        <i class="fas fa-layer-group fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">No hay listas</h5>
                        <p class="text-muted mb-4">Crea una lista manual o deja que las dinámicas se llenen solas.</p>
                        <a href="{{ route('manager.newsletter.lists.create') }}" class="btn btn-primary">+ Nueva lista</a>
                    </div>
                @endif
            </div>

            @if($lists->hasPages())
                <div class="card-footer bg-white border-top">
                    {{ $lists->links() }}
                </div>
            @endif

        </div>
    </div>

    @include('managers.includes.delete')

@endsection

@push('scripts')
<script>
$(function () {
    @if(session('success'))
        toastr.success('{{ session('success') }}');
    @endif

    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $('#delete-modal .modal-title').text($btn.data('title'));
        $('#delete-form').attr('action', $btn.data('url'));
        $('#delete-modal').modal('show');
    });
});
</script>
@endpush
