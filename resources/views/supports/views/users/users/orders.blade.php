@extends('layouts.managers')

@section('content')

    @include('supports.includes.card', ['title' => 'Ordenes - ' . $user->firstname . ' ' . $user->lastname])

    <div class="widget-content searchable-container list"
         data-bulk-url="{{ route('support.users.orders.bulk-action') }}"
         data-bulk-entity-label="orden(es)">

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
                        <th class="orders-col-checkbox"><input type="checkbox" class="form-check-input" id="select-all"></th>
                        <th scope="col">Orden</th>
                        <th scope="col">Numero</th>
                        <th scope="col">Fecha</th>
                        <th scope="col">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach ($orders as $key =>$order)
                        <tr class="search-items">

                            <td>
                                <input type="checkbox" class="form-check-input bulk-checkbox" value="{{ $order->id }}">
                            </td>
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
                                            <a class="dropdown-item d-flex align-items-center gap-3" href="{{ route('support.users.orders.view', $order->slack) }}">Visualizar</a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-3" href="{{ route('support.users.orders.edit', $order->slack) }}">Editar</a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-3" href="{{ route('support.users.orders.print', $order->slack) }}" target="_blank">Imprimir</a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-3 confirm-delete" data-href="{{ route('support.users.orders.destroy',$order->slack) }}">Eliminar</a>
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
        @if($orders->hasPages())
        <div class="card card-body mt-2">
            {{ $orders->links() }}
        </div>
        @endif
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'orden(es)',
        'bulkActions' => [
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('supports/css/views/users/users/orders.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('supports/js/views/users/users/orders.js') }}"></script>
@endpush




