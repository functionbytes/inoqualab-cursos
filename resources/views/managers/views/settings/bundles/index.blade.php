@extends('layouts.managers')

@section('title', 'Paquetes')

@section('content')


    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Paquetes de cursos</h5>
                        <p class="mb-0 text-muted">Gestiona los paquetes disponibles en la plataforma</p>
                    </div>
                    <div class="ms-auto">
                        @can('bundles.create')
                        <a href="{{ route('manager.bundles.create') }}" class="btn btn-primary">
                            Nuevo paquete
                        </a>
                        @endcan
                    </div>
                </div>
            </div>

            {{-- Search + filtros --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ Request::url() }}" id="searchForm">

                    <input type="hidden" name="available" id="filterAvailable" value="{{ $available ?? '' }}">

                    <div class="d-flex gap-2 align-items-center">
                        <div class="flex-fill">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="search" name="search" class="form-control border-start-0 ps-0"
                                       placeholder="Buscar por título..."
                                       value="{{ $searchKey ?? '' }}">
                            </div>
                        </div>

                        @php
                            $activeFilters = (int)(($available ?? '') !== '');
                        @endphp
                        <button type="button" class="btn btn-outline-secondary flex-shrink-0" title="Filtros"
                                data-bs-toggle="modal" data-bs-target="#filters-modal">
                            <i class="fas fa-sliders"></i>
                            @if($activeFilters > 0)
                                <span class="badge bg-primary ms-1">{{ $activeFilters }}</span>
                            @endif
                        </button>

                        <button type="submit" class="btn btn-primary flex-shrink-0">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Tabla --}}
            <div class="card-body">
                @if($bundles->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Titulo</th>
                                    <th>Precio</th>
                                    <th class="text-center">Cursos</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Vence</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bundles as $bundle)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                @php $thumb = $bundle->getFirstMedia('thumbnail'); @endphp
                                                @if($thumb)
                                                    <img src="{{ $thumb->getFullUrl() }}"
                                                         alt="{{ $bundle->title }}"
                                                         class="rounded"
                                                         width="38" height="38"
                                                         style="object-fit:cover;flex-shrink:0">
                                                @else
                                                    <div class="rounded bg-light d-flex align-items-center justify-content-center flex-shrink-0"
                                                         style="width:38px;height:38px">
                                                        <i class="fas fa-box-open text-muted"></i>
                                                    </div>
                                                @endif
                                                <span class="fw-semibold">
                                                    {{ Str::words($bundle->title, 10, '...') }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>${{ number_format($bundle->price, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            {{ $bundle->courses_count ?? $bundle->courses->count() }}
                                        </td>
                                        <td class="text-center">
                                            @if($bundle->available)
                                                <span class="badge bg-success-subtle text-success">Publico</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Oculto</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($bundle->expire_at)
                                                @php $expired = \Carbon\Carbon::parse($bundle->expire_at)->isPast(); @endphp
                                                <span class="{{ $expired ? 'text-danger fw-semibold' : 'text-muted' }}">
                                                    {{ \Carbon\Carbon::parse($bundle->expire_at)->format('d/m/Y') }}
                                                    @if($expired)
                                                        (vencido)
                                                    @endif
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0"
                                                        data-bs-toggle="dropdown"
                                                        data-bs-boundary="viewport">
                                                    <i class="fas fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item"
                                                           href="{{ route('bundles.view', [$bundle->slug ?? $bundle->slack]) }}"
                                                           target="_blank">
                                                            Ver en sitio
                                                        </a>
                                                    </li>
                                                    @can('bundles.update')
                                                    <li>
                                                        <a class="dropdown-item"
                                                           href="{{ route('manager.bundles.edit', $bundle->slack) }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item btn-toggle-available" href="javascript:void(0)"
                                                           data-slack="{{ $bundle->slack }}"
                                                           data-available="{{ $bundle->available }}">
                                                            {{ $bundle->available ? 'Ocultar' : 'Publicar' }}
                                                        </a>
                                                    </li>
                                                    @endcan
                                                    @can('bundles.delete')
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item btn-delete" href="#"
                                                           data-url="{{ route('manager.bundles.destroy', $bundle->slack) }}"
                                                           data-title="Eliminar: {{ $bundle->title }}">
                                                            Eliminar
                                                        </a>
                                                    </li>
                                                    @endcan
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
                        <i class="fas fa-box-open fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">
                            @if(($searchKey ?? '') !== '' || ($available ?? '') !== '')
                                No se encontraron resultados
                            @else
                                No hay paquetes
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if(($searchKey ?? '') !== '' || ($available ?? '') !== '')
                                No hay paquetes que coincidan con los filtros aplicados.
                            @else
                                Crea el primer paquete de cursos de la plataforma.
                            @endif
                        </p>
                        @if(($searchKey ?? '') !== '' || ($available ?? '') !== '')
                            <a href="{{ Request::url() }}" class="btn btn-outline-secondary">
                                Ver todos
                            </a>
                        @else
                            @can('bundles.create')
                            <a href="{{ route('manager.bundles.create') }}" class="btn btn-primary">
                                Nuevo paquete
                            </a>
                            @endcan
                        @endif
                    </div>
                @endif
            </div>

            @if($bundles->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        Mostrando {{ $bundles->firstItem() }}–{{ $bundles->lastItem() }} de {{ $bundles->total() }} paquetes
                    </span>
                    {{ $bundles->appends(request()->input())->links() }}
                </div>
            @endif

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
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Estado</label>
                        <select id="modalAvailable" class="form-select">
                            <option value="">Todos</option>
                            <option value="1" {{ ($available ?? '') === '1' ? 'selected' : '' }}>Publico</option>
                            <option value="0" {{ ($available ?? '') === '0' ? 'selected' : '' }}>Oculto</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button type="button" id="applyFiltersBtn" class="btn btn-primary w-100 mb-2">
                        Aplicar filtros
                    </button>
                    <a href="{{ Request::url() }}" class="btn btn-secondary w-100">
                        Limpiar filtros
                    </a>
                </div>
            </div>
        </div>
    </div>

    @include('managers.includes.delete')

@endsection

@push('scripts')
<script>
$(function () {

    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    @if(session('success'))
        toastr.success('{{ session('success') }}');
    @endif
    @if(session('error'))
        toastr.error('{{ session('error') }}');
    @endif

    // ── Filters modal ────────────────────────────────────────────────────────
    $('#applyFiltersBtn').on('click', function () {
        $('#filterAvailable').val($('#modalAvailable').val());
        $('#filters-modal').modal('hide');
        $('#searchForm').submit();
    });

    // ── Toggle disponibilidad individual ────────────────────────────────────
    $(document).on('click', '.btn-toggle-available', function () {
        var slack = $(this).data('slack');

        $.ajax({
            url: '{{ route('manager.bundles.toggle') }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: { slack: slack },
            success: function (res) {
                if (res.success) {
                    toastr.success(res.message, 'Listo', { positionClass: 'toast-bottom-right', progressBar: true, closeButton: true });
                    setTimeout(function () { location.reload(); }, 1200);
                }
            },
            error: function () {
                toastr.error('Error al cambiar el estado.');
            }
        });
    });

    // ── Eliminar individual via modal ────────────────────────────────────────
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
