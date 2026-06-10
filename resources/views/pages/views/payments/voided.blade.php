@extends('layouts.pages')

@section('title', 'Pago rechazado')

@section('content')

<main class="cartx">
    <section class="cartx-checkout">
        <div class="container">
            <div class="res-wrap cartx-reveal">

                <div class="res-icon no"><i class="fas fa-xmark"></i></div>
                <h2>La transacción de la orden <span class="o">#{{ $order->reference ?? $order->slack }}</span> fue rechazada</h2>
                <p class="res-msg">Esto suele ocurrir por fondos insuficientes o restricciones del banco. Si el problema continúa, escríbenos y te ayudamos.</p>

                <div class="res-actions">
                    <a class="primary" href="{{ route('checkout.cart') }}">Reintentar pago</a>
                    <a class="whatsapp" href="https://wa.me/573152880890" target="_blank" rel="noopener">Escríbenos por WhatsApp</a>
                    <a class="ghost" href="{{ route('courses') }}">Volver a cursos</a>
                </div>

            </div>
        </div>
    </section>
</main>

@endsection
