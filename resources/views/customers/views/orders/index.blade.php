@extends('layouts.customers')

@section('content')

    @include('customers.includes.card', ['title' => 'Órdenes'])

    <div class="widget-content searchable-container list">
        
        <div class="card card-body">
            <div class="row">
                <div class="col-md-12 col-xl-12">
                    <form class="position-relative form-search" action="{{ Request::fullUrl() }}" method="GET">
                        <div class="row justify-content-between g-2 ">
                            <div class="col-auto flex-grow-1">
                                <div class="tt-search-box">
                                    <div class="input-group">
                                        <span class="position-absolute top-50 start-0 translate-middle-y ms-2"> <i class="fas fa-magnifying-glass"></i></span>
                                        <input class="form-control rounded-start w-100 ps-5" type="text" id="search" name="search" placeholder="Buscar" @isset($searchKey) value="{{ $searchKey }}" @endisset>
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
                        <th>Total</th>
                        <th>Estado pago</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                   
                    @forelse ($orders as $key =>$order)
                        <tr class="search-items">

                            <td>
                                <span class="usr-email-addr" data-email="{{$order->slack }}">{{$order->slack }}</span>
                            </td> <td>
                                <span class="usr-email-addr">$ {{ number_format($order->total_order_amount ?? $order->total, 0, ',', '.') }}</span>
                            </td>
                            <td>
                                    <span class="badge bg-light-{{$order->condition->slug }} rounded-3 py-2 text-primary fw-semibold fs-2 d-inline-flex align-items-center gap-1">

                                        {{ $order->condition->title }}
                                    </span>
                            </td>
                            <td>
                                <span class="usr-ph-no" data-phone="{{ date('Y-m-d', strtotime($order->updated_at)) }}">{{ date('Y-m-d', strtotime($order->updated_at)) }}</span>
                            </td>
                            <td class="text-center">
                                <div class="dropdown dropstart">
                                    <button type="button" class="btn btn-link text-muted p-0" id="dropdownMenuButton{{ $order->id }}" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Acciones de la orden">
                                        <i class="fas fa-ellipsis-vertical" aria-hidden="true"></i>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $order->id }}">

                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-3" href="{{ route('customers.orders.view',$order->slack) }}"><i class="fa-duotone fa-money-check-pen"></i>Visualizar</a>
                                        </li>

                                        @if (in_array($order->condition_id, [1, 2]) && $order->total_order_amount > 0)
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-3 text-primary" href="{{ route('customers.orders.payments', $order->slack) }}"><i class="fa-duotone fa-credit-card"></i>Pagar ahora</a>
                                        </li>
                                        @endif

                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fa-duotone fa-cart-shopping fa-2x d-block mb-3 opacity-50"></i>
                                <span class="fw-semibold d-block">No tienes pedidos</span>
                                <small>Aún no has realizado ninguna compra.</small>
                            </td>
                        </tr>
                    @endforelse

                    </tbody>
                </table>
            </div>
            @if ($orders->total() > 0)
            <div class="result-body ">
                <span>Mostrando {{ $orders->firstItem() }}-{{ $orders->lastItem() }} de {{ $orders->total() }} resultados</span>
                <nav>
                    {{ $orders->appends(request()->input())->links() }}
                </nav>
            </div>
            @endif
        </div>
    </div>
@endsection




