@extends('layouts.pages')

@section('title', 'Pago pendiente')

@section('content')

<main class="cartx">
    <section class="cartx-checkout">
        <div class="container">
            <div class="res-wrap cartx-reveal">

                <div class="res-icon pend"><i class="fas fa-clock"></i></div>
                <h2>Tu orden <span class="o">#{{ $order->reference ?? $order->slack }}</span> está pendiente</h2>
                <p class="res-msg">Algunos medios de pago (como PSE) pueden tardar unos minutos. No es necesario volver a pagar; te avisaremos por correo cuando se confirme.</p>

                <div class="res-card">
                    <div class="res-card-head">
                        <span class="t">Resumen de tu compra</span>
                        <span class="res-paid pend">
                            <i class="fas fa-clock"></i> Pendiente
                        </span>
                    </div>

                    @foreach($order->items as $it)
                        @php
                            $isBundle = $it->item_type === \App\Models\Bundle\Bundle::class;
                            $entity = $isBundle
                                ? \App\Models\Bundle\Bundle::find($it->item_id)
                                : \App\Models\Course\Course::find($it->item_id);
                        @endphp
                        <div class="res-line">
                            <span class="res-tag">{{ $isBundle ? 'Paquete' : 'Curso' }}</span>
                            <div class="rl-row">
                                <span class="rl-name">{{ $entity->title ?? 'Producto' }}@if($it->quantity > 1) · Cant: {{ (int) $it->quantity }}@endif</span>
                                <span class="rl-price">${{ number_format($it->amount, 0, ',', '.') }} COP</span>
                            </div>
                        </div>
                    @endforeach

                    <div class="res-tot">
                        @if($order->total_discount_amount > 0)
                            <div class="res-tr">
                                <span>Subtotal</span>
                                <b>${{ number_format($order->total_before_discount, 0, ',', '.') }} COP</b>
                            </div>
                            <div class="res-tr disc">
                                <span>Descuento{{ optional($order->coupon)->code ? ' ('.$order->coupon->code.')' : '' }}</span>
                                <b>– ${{ number_format($order->total_discount_amount, 0, ',', '.') }} COP</b>
                            </div>
                        @endif
                        <div class="res-tr total">
                            <span>Total</span>
                            <b>${{ number_format($order->total_order_amount, 0, ',', '.') }} COP</b>
                        </div>
                    </div>
                </div>

                <div class="res-steps">
                    <div class="res-step done">
                        <div class="dot"><i class="fas fa-check"></i></div>
                        <div class="lbl">Pago recibido</div>
                    </div>
                    <div class="res-step active">
                        <div class="dot"><i class="fas fa-clock"></i></div>
                        <div class="lbl">Verificando</div>
                    </div>
                    <div class="res-step">
                        <div class="dot"><i class="fas fa-graduation-cap"></i></div>
                        <div class="lbl">Acceso al curso</div>
                    </div>
                </div>

                <div class="res-help">
                    <i class="fas fa-circle-info"></i>
                    Tiempo estimado de confirmación: <b>5–30 minutos</b>
                </div>

                <div class="res-actions">
                    @if(auth()->check() && auth()->id() === $order->user_id)
                        <a class="primary" href="{{ route('customers.orders') }}">Ver mis órdenes</a>
                    @endif
                    <a class="ghost" href="{{ route('index') }}">Volver al inicio</a>
                </div>

            </div>
        </div>
    </section>
</main>

@endsection
