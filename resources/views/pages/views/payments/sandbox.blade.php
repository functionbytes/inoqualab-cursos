@extends('layouts.pages')

@section('title', 'Pago de prueba (Sandbox)')

@push('css')
    <link rel="stylesheet" href="{{ asset('pages/css/views/payments/payments.css') }}">
@endpush

@section('content')

<main class="cartx">
    <div class="cartx-band">
        <div class="container">
            <div class="cartx-crumb">INICIO <span class="sep">/</span> <span class="cur">PAGO SANDBOX</span></div>
            <h1>Pago en modo prueba</h1>
            <p class="lede">Estás en el entorno sandbox. Ningún cobro es real: simula el resultado del pago.</p>
        </div>
    </div>

    <section class="cartx-checkout">
        <div class="container">
            <div class="cartx-grid">

                {{-- Resumen + tarjetas de prueba --}}
                <div class="cartx-reveal">
                    <div class="cart-page-card">
                        <h2 class="cp-title">Orden <span>#{{ $order->reference ?? $order->slack }}</span></h2>
                        <p class="cp-sub">Revisa el detalle antes de simular el pago.</p>

                        @foreach($order->items as $it)
                            @php
                                $isBundle = $it->item_type === \App\Models\Bundle\Bundle::class;
                                $entity = $isBundle ? \App\Models\Bundle\Bundle::find($it->item_id) : \App\Models\Course\Course::find($it->item_id);
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

                        <div class="sandbox sandbox--spaced">
                            <div class="sandbox-head"><span class="sandbox-badge"><i class="fas fa-flask"></i> Modo prueba</span><span class="ttl">Tarjetas de prueba Wompi</span></div>
                            <div class="sandbox-body">
                                <p class="intro">En un entorno con llaves Wompi válidas usarías estas tarjetas en la pasarela. Aquí puedes simular el resultado directamente.</p>
                                <div class="test-list">
                                    <div class="test-row ok"><span class="dot"></span><div class="info"><div class="h"><b>Aprobada</b></div><div class="meta"><span class="chip">4242 4242 4242 4242</span><span class="chip"><span class="k">CVV</span>123</span><span class="chip"><span class="k">Exp</span>12/29</span></div></div></div>
                                    <div class="test-row no"><span class="dot"></span><div class="info"><div class="h"><b>Declinada</b></div><div class="meta"><span class="chip">4111 1111 1111 1111</span></div></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Acciones de simulación --}}
                <div class="cartx-summary-col cartx-reveal">
                    <div class="summary">
                        <div class="summary-head">
                            <div class="eyebrow">Total a pagar</div>
                            <h3>${{ number_format($order->total_order_amount, 0, ',', '.') }} COP</h3>
                        </div>
                        <div class="summary-body">
                            <p class="cp-sub cp-sub--roomy">Simula el resultado del pago para continuar:</p>

                            <a class="go-pay go-pay--approved" href="{{ route('checkout.simulate', [$order->slack, 'APPROVED']) }}">
                                <i class="fas fa-check"></i> Simular pago aprobado
                            </a>
                            <a class="go-pay go-pay--spaced" href="{{ route('checkout.simulate', [$order->slack, 'PENDING']) }}">
                                <i class="fas fa-clock"></i> Simular pendiente
                            </a>
                            <a class="go-pay go-pay--spaced go-pay--declined" href="{{ route('checkout.simulate', [$order->slack, 'DECLINED']) }}">
                                <i class="fas fa-xmark"></i> Simular declinado
                            </a>

                            <div class="trust"><i class="fas fa-shield-halved"></i> Entorno seguro de pruebas · Wompi sandbox</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</main>
@endsection
