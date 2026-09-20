@extends('layouts.pages')

@section('title', 'Pago')

@push('css')
    <link rel="stylesheet" href="{{ asset('pages/css/views/payments/payments.css') }}">
@endpush

@section('content')

<main class="cartx">
    <div class="cartx-band">
        <div class="container">
            <div class="cartx-crumb">INICIO <span class="sep">/</span> <span class="cur">PAGO</span></div>
            <h1>Completa tu pago</h1>
            <p class="lede">Paga de forma segura con Wompi. Elige pagar aquí mismo o en la página de Wompi.</p>
        </div>
    </div>

    <section class="cartx-checkout">
        <div class="container">
            <div class="cartx-grid">

                {{-- Resumen --}}
                <div class="cartx-reveal">
                    <div class="cart-page-card">
                        <h2 class="cp-title">Orden <span>#{{ $order->reference ?? $order->slack }}</span></h2>
                        <p class="cp-sub">Revisa el detalle antes de pagar.</p>

                        @foreach($order->items as $it)
                            @php
                                $isBundle = $it->item_type === \App\Models\Bundle\Bundle::class;
                                $entity = $it->itemable;
                            @endphp
                            <div class="cp-item cp-item--summary">
                                <div class="cp-prod">
                                    <div class="cp-info">
                                        <span class="cp-tag">{{ $isBundle ? 'Paquete' : 'Curso' }}</span>
                                        <div class="cp-name">{{ $entity->title ?? 'Producto' }}@if($it->quantity > 1)<small>Cantidad: {{ (int) $it->quantity }}</small>@endif</div>
                                    </div>
                                </div>
                                <div class="cp-price-cell"><div class="amt">${{ number_format($it->amount, 0, ',', '.') }} COP</div></div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Opciones de pago --}}
                <div class="cartx-summary-col cartx-reveal">
                    <div class="summary">
                        <div class="summary-head">
                            <div class="eyebrow">Total a pagar</div>
                            <h3>${{ number_format($order->total_order_amount, 0, ',', '.') }} COP</h3>
                        </div>
                        <div class="summary-body">

                            {{-- ===== Opción A: Widget embebido ===== --}}
                            <p class="cp-sub cp-sub--tight"><b>Opción 1 — Pago rápido</b></p>
                            <div class="payment-widget">
                                @include('pages.partials.sections.payments.wompi')
                            </div>

                            {{-- ===== Opción B: Web Checkout (redirección) ===== --}}
                            <div class="pay-or"><span>o</span></div>
                            <a href="{{ $checkoutUrl }}" class="go-pay">
                                <i class="fas fa-arrow-up-right-from-square"></i> Pagar en la página de Wompi
                            </a>

                            <div class="trust"><i class="fas fa-shield-halved"></i> Pago 100% seguro procesado con Wompi</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</main>
@endsection

