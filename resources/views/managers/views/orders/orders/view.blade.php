@extends('layouts.managers')

@section('title', 'Orden ' . $order->slack)

@section('page_header')
    @include('managers.includes.card', [
        'title' => 'Orden ' . $order->slack,
        'breadcrumbs' => [
            ['label' => 'Órdenes', 'url' => route('manager.orders')],
            ['label' => $order->slack],
        ],
    ])
@endsection

@section('content')

    @php
        $conditionBadge = match ((int) $order->condition_id) {
            4 => ['bg' => 'invoice-status--paid', 'label' => $order->condition->title],
            3 => ['bg' => 'invoice-status--rejected', 'label' => $order->condition->title],
            2 => ['bg' => 'invoice-status--pending', 'label' => $order->condition->title],
            default => ['bg' => 'invoice-status--draft', 'label' => $order->condition->title],
        };
    @endphp

    <div class="row">
        <div class="col-lg-12">
            <div class="invoice-card">

                {{-- Encabezado --}}
                <div class="invoice-header">
                    <div class="invoice-brand">
                        <div class="invoice-brand-name">{{ Str::upper(config('app.name')) }}</div>
                        <div class="invoice-brand-sub">E-Learnings</div>
                    </div>
                    <div class="invoice-meta">
                        <div class="invoice-meta-title">ORDEN {{ $order->slack }}</div>
                        <div class="invoice-meta-sub">Referencia {{ $order->reference }}</div>
                        <div class="invoice-meta-sub">Emitida el {{ date('d/m/Y', strtotime($order->created_at)) }}</div>
                        <span class="invoice-status-badge {{ $conditionBadge['bg'] }}">{{ $conditionBadge['label'] }}</span>
                    </div>
                </div>

                {{-- Partes --}}
                <div class="invoice-parties">

                    <div class="invoice-party">
                        <div class="invoice-party-label">Facturar a</div>
                        <div class="invoice-party-name">{{ Str::upper($order->user?->firstname ?? 'Usuario eliminado') }} {{ Str::upper($order->user?->lastname ?? '') }}</div>
                        <div class="invoice-party-line">Doc. {{ Str::upper(Str::lower($order->user?->identification ?? '—')) }}</div>
                        @if($order->user?->address)
                            <div class="invoice-party-line">{{ Str::upper(Str::lower($order->user->address)) }}</div>
                        @endif
                        @if($order->user?->cellphone)
                            <div class="invoice-party-line">{{ Str::upper(Str::lower($order->user->cellphone)) }}</div>
                        @endif
                    </div>

                    @if($order->activity != null)
                        <div class="invoice-party">
                            <div class="invoice-party-label">Vendido por cuenta de</div>
                            <div class="invoice-party-name">{{ Str::upper($order->activity->distributor->title ?? 'N/D') }}</div>
                            @if($order->activity->distributor->nit ?? null)
                                <div class="invoice-party-line">NIT {{ $order->activity->distributor->nit }}</div>
                            @endif
                            <div class="invoice-party-line">{{ Str::upper($order->activity->enterprise->title ?? 'N/D') }}</div>
                            <div class="invoice-party-line">Encargado: {{ Str::upper($order->activity->staff->firstname ?? 'N/D') }} {{ Str::upper($order->activity->staff->lastname ?? '') }}</div>
                        </div>
                    @endif

                    <div class="invoice-party">
                        <div class="invoice-party-label">Pago</div>
                        <div class="invoice-party-line">{{ Str::upper($order->type->title) }} · {{ Str::upper($order->method->title) }}</div>
                        @if($order->payment_at != null)
                            <div class="invoice-party-line">Pagada el {{ date('d/m/Y', strtotime($order->payment_at)) }}</div>
                        @endif
                    </div>

                </div>

                {{-- Ítems --}}
                <div class="table-responsive invoice-table-wrap">
                    <table class="table invoice-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Descripción</th>
                                <th class="text-center">Cant.</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                @php $isBundle = $item->itemable instanceof \App\Models\Bundle\Bundle; @endphp
                                <tr>
                                    <td>
                                        @if($item->itemable)
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <span class="fw-semibold">{{ $item->itemable->title }}</span>
                                                @if($isBundle)
                                                    <span class="badge invoice-item-badge">Paquete</span>
                                                @else
                                                    <span class="badge invoice-item-badge">Curso</span>
                                                @endif
                                            </div>
                                            @if($isBundle && $item->itemable->courses->isNotEmpty())
                                                <small class="text-muted d-block mt-1">
                                                    Incluye {{ $item->itemable->courses->count() }} cursos: {{ Str::limit($item->itemable->courses->pluck('title')->implode(', '), 90) }}
                                                </small>
                                            @endif
                                        @else
                                            <span class="fw-semibold text-danger">Producto no encontrado</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ ceil($item->quantity) }}</td>
                                    <td class="text-end fw-semibold">$ {{ number_format($item->amount) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Totales --}}
                <div class="invoice-totals-wrap">
                    <div class="invoice-totals-box">
                        @if($order->total_discount_amount > 0)
                            <div class="invoice-totals-row">
                                <span>Descuentos</span>
                                <span>$ {{ number_format($order->total_discount_amount) }}</span>
                            </div>
                        @endif
                        @if($order->total_tax_amount > 0)
                            <div class="invoice-totals-row">
                                <span>Impuestos</span>
                                <span>$ {{ number_format($order->total_tax_amount) }}</span>
                            </div>
                        @endif
                        <div class="invoice-totals-row">
                            <span>Subtotal</span>
                            <span>$ {{ number_format($order->total_after_discount) }}</span>
                        </div>
                        <div class="invoice-totals-row invoice-totals-row--grand">
                            <span>TOTAL</span>
                            <span>$ {{ number_format($order->total_order_amount) }}</span>
                        </div>
                    </div>
                </div>

                <div class="invoice-footer-note">
                    Gracias por confiar en {{ Str::upper(config('app.name')) }}. Este documento es un comprobante de la orden generada en el sistema.
                </div>

            </div>
        </div>
    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/orders/orders/view.css') }}">
@endpush
