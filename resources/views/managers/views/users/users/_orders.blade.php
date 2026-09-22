{{--
    Partial AJAX: busqueda + tabla + paginacion de ordenes de un usuario.

    Se incluye normalmente desde orders.blade.php (dentro de #ajax-table-root)
    para el render inicial, y el controller devuelve ESTE mismo partial (sin
    layout) cuando la peticion es AJAX ($request->ajax()) al paginar. Ver
    public/managers/js/ajax-table.js.
--}}
<div class="card card-body">
    <div class="row">
        <div class="col-md-12 col-xl-12">
            <form class="position-relative form-search" action="{{ Request::url() }}" method="GET" id="searchForm">
                <div class="row justify-content-between g-2 ">
                    <div class="col-auto flex-grow-1">
                        <div class="tt-search-box">
                            <div class="input-group">
                                <span class="position-absolute top-50 start-0 translate-middle-y ms-2"> <i class="fas fa-magnifying-glass"></i></span>
                                <input class="form-control rounded-start w-100" type="text" id="search" name="search" placeholder="Buscar" @isset($searchKey) value="{{ $searchKey }}" @endisset>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Buscar">
                            <i class="fa-duotone fa-magnifying-glass"></i>
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
<div class="card card-body">
    <div class="table-responsive">
        <table class="table search-table align-middle text-nowrap">
            <thead class="header-item">
            <tr>
                <th>Orden</th>
                <th>Numero</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>

            @foreach ($orders as $key =>$order)
                <tr class="search-items">

                    <td>
                        <span class="usr-email-addr" data-email="{{$order->slack }}">{{$order->slack }}</span>
                    </td>
                    <td>
                        <span class="usr-email-addr" data-email="{{$order->reference }}">{{$order->reference }}</span>
                    </td>

                    <td>
                        <span class="usr-ph-no" data-phone="{{ date('Y-m-d', strtotime($order->updated_at)) }}">{{ date('Y-m-d', strtotime($order->updated_at)) }}</span>
                    </td>
                    <td class="text-left">
                        <div class="dropdown dropstart">
                            <a href="#" class="text-muted" id="dropdownMenuButton-{{ $loop->index }}" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-vertical fs-5"></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-{{ $loop->index }}">

                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-3 confirm-delete" data-href="{{ route('manager.enterprises.users.orders.destroy',$order->slack) }}">Eliminar</a>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @endforeach

            </tbody>
        </table>
    </div>

</div>
<div class="card">
    @include('managers.includes.pagination-footer', [
        'paginator' => $orders,
        'itemLabel' => 'órdenes',
    ])
</div>
