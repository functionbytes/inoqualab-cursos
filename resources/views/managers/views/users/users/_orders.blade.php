{{--
    Partial AJAX: busqueda + tabla + paginacion de ordenes de un usuario.

    Se incluye normalmente desde orders.blade.php (dentro de #ajax-table-root)
    para el render inicial, y el controller devuelve ESTE mismo partial (sin
    layout) cuando la peticion es AJAX ($request->ajax()) al buscar o paginar.
    Ver public/managers/js/ajax-table.js.
--}}
<div class="card">

    {{-- Search --}}
    <div class="card-body border-bottom">
        <form method="GET" action="{{ Request::url() }}" id="searchForm">
            @include('managers.includes.filter-toolbar', [
                'searchName' => 'search',
                'searchValue' => $searchKey ?? '',
                'searchPlaceholder' => 'Buscar por número de orden...',
            ])
        </form>
    </div>

    {{-- Tabla --}}
    <div class="card-body">
        @if($orders->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle text-nowrap mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Orden</th>
                            <th>Numero</th>
                            <th class="text-center">Fecha</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td>
                                    <span class="fw-semibold">{{ $order->slack }}</span>
                                </td>
                                <td>
                                    {{ $order->reference }}
                                </td>
                                <td class="text-center">
                                    <span class="text-muted">{{ date('d/m/Y', strtotime($order->updated_at)) }}</span>
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
                                                <a class="dropdown-item confirm-delete" href="#"
                                                   data-href="{{ route('manager.enterprises.users.orders.destroy', $order->slack) }}">
                                                    Eliminar
                                                </a>
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
                <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-invoice', 48) !!}</div>
                <h5 class="fw-bold mb-2">
                    @if(($searchKey ?? '') !== '')
                        No se encontraron resultados
                    @else
                        No hay órdenes
                    @endif
                </h5>
                <p class="text-muted mb-0">
                    @if(($searchKey ?? '') !== '')
                        No hay órdenes que coincidan con la búsqueda.
                    @else
                        Las órdenes de este usuario aparecerán aquí cuando se registren.
                    @endif
                </p>
            </div>
        @endif
    </div>

    @include('managers.includes.pagination-footer', [
        'paginator' => $orders,
        'itemLabel' => 'órdenes',
    ])

</div>
