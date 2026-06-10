@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">
            <div class="card w-100">
                <div class="card-body border-top">
                    <div class="d-flex no-block align-items-center mb-3">
                        <h5 class="mb-0">Órdenes del distribuidor</h5>
                    </div>

                    <form method="GET" class="row g-2 mb-4">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control" placeholder="Buscar por código..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="methods" class="form-select">
                                <option value="">Todos los métodos</option>
                                @foreach($methods as $id => $title)
                                    <option value="{{ $id }}" {{ request('methods') == $id ? 'selected' : '' }}>{{ $title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="condition" class="form-select">
                                <option value="">Todas las condiciones</option>
                                @foreach($conditions as $id => $title)
                                    <option value="{{ $id }}" {{ request('condition') == $id ? 'selected' : '' }}>{{ $title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="type" class="form-select">
                                <option value="">Todos los tipos</option>
                                @foreach($types as $id => $title)
                                    <option value="{{ $id }}" {{ request('type') == $id ? 'selected' : '' }}>{{ $title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Código</th>
                                    <th>Total</th>
                                    <th>Condición</th>
                                    <th>Fecha</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $order->slack }}</td>
                                    <td>{{ formatPrice($order->total) }}</td>
                                    <td>{{ optional($order->condition)->title ?? '-' }}</td>
                                    <td>{{ $order->created_at->format('d/m/Y') }}</td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="{{ route('manager.orders.view', $order->slack) }}">Ver detalle</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No hay órdenes registradas.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $orders->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
