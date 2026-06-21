@extends('layouts.customers')

@section('content')

    @include('customers.includes.card', ['title' => 'Detalle orden '. $order->slack])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-4">

                    {{-- Encabezado --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p class="mb-1"><span class="fw-semibold">Orden:</span> {{ $order->slack }}</p>
                            <p class="mb-1"><span class="fw-semibold">Cliente:</span>
                                {{ Str::ucfirst(Str::lower($order->user->firstname)) }}
                                {{ Str::ucfirst(Str::lower($order->user->lastname)) }}
                            </p>
                            @if($order->user->address)
                                <p class="mb-1"><span class="fw-semibold">Dirección:</span>
                                    {{ Str::ucfirst(Str::lower($order->user->address)) }}
                                </p>
                            @endif
                        </div>
                        <div class="col-md-6 text-md-end">
                            @if($order->payment_at)
                                <p class="mb-1"><span class="fw-semibold">Fecha de pago:</span>
                                    {{ \Carbon\Carbon::parse($order->payment_at)->format('d/m/Y') }}
                                </p>
                            @endif
                            @if($order->inscription)
                                @if($order->inscription->enroll_start)
                                    <p class="mb-1"><span class="fw-semibold">Inicio acceso:</span>
                                        {{ \Carbon\Carbon::parse($order->inscription->enroll_start)->format('d/m/Y') }}
                                    </p>
                                @endif
                                @if($order->inscription->enroll_expire)
                                    <p class="mb-1"><span class="fw-semibold">Vence acceso:</span>
                                        {{ \Carbon\Carbon::parse($order->inscription->enroll_expire)->format('d/m/Y') }}
                                    </p>
                                @endif
                            @endif
                            @if($order->condition)
                                <span class="badge bg-light-{{ $order->condition->slug }} text-{{ $order->condition->slug }} rounded-3 py-2 px-3 fw-semibold">
                                    {{ $order->condition->title }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <hr>

                    {{-- Items --}}
                    <div class="table-responsive mb-4">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-end">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($order->items as $item)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold">{{ optional($item->itemable)->title ?? 'Producto' }}</span>
                                            @if(optional(optional($item->itemable)->categorie)->title)
                                                <p class="mb-0 text-muted small">{{ $item->itemable->categorie->title }}</p>
                                            @endif
                                        </td>
                                        <td class="text-end fw-semibold">${{ number_format($item->amount, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-muted text-center">Sin items</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Resumen de totales --}}
                    <div class="d-flex flex-column align-items-end gap-1">
                        @if($order->total_discount_amount > 0)
                            <div class="d-flex gap-4">
                                <span class="text-muted">Descuento</span>
                                <span class="fw-semibold text-success">-${{ number_format($order->total_discount_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="d-flex gap-4 fs-5">
                            <span class="fw-bold">Total</span>
                            <span class="fw-bold">${{ number_format($order->total_order_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Acción de pago si está pendiente --}}
                    @if(in_array($order->condition_id, [1, 2, 3]) && $order->total_order_amount > 0)
                        <div class="mt-4 border-top pt-3">
                            <a href="{{ route('customers.orders.payments', $order->slack) }}"
                               class="btn btn-primary">
                                <i class="fa-solid fa-credit-card me-1"></i> {{ $order->condition_id == 3 ? 'Reintentar pago' : 'Pagar ahora' }}
                            </a>
                        </div>
                    @endif

                    {{-- Descarga de recibo si está pagada --}}
                    @if($order->condition_id === 4)
                        <div class="mt-4 border-top pt-3">
                            <a href="{{ route('customers.orders.invoice', $order->slack) }}"
                               class="btn btn-outline-primary">
                                <i class="fa-solid fa-file-arrow-down me-1"></i> Descargar recibo
                            </a>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

@endsection
