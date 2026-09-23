@extends('layouts.managers')

@section('page_header')
    @php ob_start(); @endphp
    <a href="{{ route('distributor.orders.print', $order->slack) }}" class="btn btn-primary btn-icon" title="Imprimir" aria-label="Imprimir" target="_blank">
        <i class="fas fa-print"></i>
    </a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('distributors.includes.card', [
        'title' => 'Orden '.$order->reference,
        'description' => 'Detalle de la orden '.$order->slack,
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    <div class="order-view">

        <div class="d-flex justify-content-end mb-3">
            @if($order->payment_at != null)
                <span class="badge order-status-badge order-status-paid">
                    <span class="order-status-dot"></span>Pagada
                </span>
            @else
                <span class="badge order-status-badge order-status-pending">
                    <span class="order-status-dot"></span>Pendiente
                </span>
            @endif
        </div>

        <div class="row g-3">

            {{-- Columna izquierda: cliente / gestión / pago --}}
            <div class="col-lg-4">

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="order-avatar rounded-circle bg-light-primary text-primary d-flex align-items-center justify-content-center fw-bold">
                                {{ Str::upper(Str::substr($order->user->firstname ?? 'N', 0, 1)).Str::upper(Str::substr($order->user->lastname ?? 'D', 0, 1)) }}
                            </div>
                            <div>
                                <div class="text-muted text-uppercase small fw-semibold">Cliente</div>
                                <div class="fw-semibold">{{Str::upper(($order->user->firstname ?? 'N/D'))}} {{Str::upper(($order->user->lastname ?? ''))}}</div>
                            </div>
                        </div>

                        <div class="order-kv-stack border-top pt-2">
                            <span class="text-muted small">Identificación</span>
                            <span class="fw-semibold d-block">{{Str::upper(Str::lower(($order->user->identification ?? 'N/D')))}}</span>
                        </div>
                        @if(($order->user->address ?? null) != null)
                            <div class="order-kv-stack border-top pt-2">
                                <span class="text-muted small">Dirección</span>
                                <span class="fw-semibold d-block">{{Str::upper(Str::lower(($order->user->address ?? '')))}}</span>
                            </div>
                        @endif
                        @if(($order->user->cellphone ?? null) != null)
                            <div class="order-kv-stack border-top pt-2 mb-0">
                                <span class="text-muted small">Celular</span>
                                <span class="fw-semibold d-block">{{Str::upper(Str::lower(($order->user->cellphone ?? '')))}}</span>
                            </div>
                        @endif
                    </div>
                </div>

                @if($order->activity != null)
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="text-muted text-uppercase small fw-semibold mb-3">Gestión</div>
                            <div class="order-kv-stack">
                                <span class="text-muted small">Distribuidor</span>
                                <span class="fw-semibold d-block">{{Str::upper($order->activity->distributor->title)}}</span>
                            </div>
                            <div class="order-kv-stack">
                                <span class="text-muted small">Empresa</span>
                                <span class="fw-semibold d-block">{{Str::upper($order->activity->enterprise->title)}}</span>
                            </div>
                            <div class="order-kv-stack mb-0">
                                <span class="text-muted small">Encargado</span>
                                <span class="fw-semibold d-block">{{Str::upper($order->activity->staff->firstname)}} {{Str::upper($order->activity->staff->lastname)}}</span>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <div class="text-muted text-uppercase small fw-semibold mb-3">Pago</div>
                        <div class="d-flex justify-content-between border-top pt-2">
                            <span class="text-muted">Tipo de pago</span>
                            <span class="fw-semibold">{{ Str::upper($order->type->title)}}</span>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-2">
                            <span class="text-muted">Metodo de pago</span>
                            <span class="fw-semibold">{{ Str::upper($order->method->title)}}</span>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-2">
                            <span class="text-muted">Fecha de creación</span>
                            <span class="fw-semibold">{{ date('Y-m-d', strtotime($order->created_at)) }}</span>
                        </div>
                        @if($order->payment_at != null)
                            <div class="d-flex justify-content-between border-top pt-2">
                                <span class="text-muted">Fecha de pago</span>
                                <span class="fw-semibold">{{ date('Y-m-d', strtotime($order->payment_at)) }}</span>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            {{-- Columna derecha: ítems + resumen --}}
            <div class="col-lg-8">

                <div class="card mb-3">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">Ítems de la orden</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" class="fw-bolder text-uppercase">Descripción</th>
                                        <th scope="col" class="fw-bolder text-uppercase text-center order-col-qty">Cantidad</th>
                                        <th scope="col" class="fw-bolder text-uppercase text-end order-col-total">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                        <tr>
                                            <td>
                                                @if($item->itemable instanceof \App\Models\Course\Course)
                                                    <span class="fw-semibold">{{ $item->itemable->title }}</span>
                                                @elseif($item->itemable instanceof \App\Models\Bundle\Bundle)
                                                    <span class="fw-semibold">{{ $item->itemable->title }}</span>
                                                @else
                                                    <span class="fw-semibold text-danger">Item not found</span>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ ceil($item->quantity) }}</td>
                                            <td class="text-end fw-semibold">${{ number_format($item->amount) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="order-summary-card">
                    @if($order->total_discount_amount > 0)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Descuentos</span>
                            <span>-${{ number_format($order->total_discount_amount) }}</span>
                        </div>
                    @endif
                    @if($order->total_tax_amount > 0)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Impuestos</span>
                            <span>${{ number_format($order->total_tax_amount) }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span>${{ number_format($order->total_after_discount) }}</span>
                    </div>
                    <div class="order-summary-total d-flex justify-content-between align-items-baseline">
                        <span>Total de la orden</span>
                        <span class="order-summary-total-amount">${{ number_format($order->total_order_amount) }}</span>
                    </div>
                </div>

            </div>

        </div>
    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('distributors/css/views/orders/orders/view.css') }}">
@endpush
