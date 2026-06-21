@extends('layouts.pages')

@section('title', 'Pago aprobado')

@section('content')

<main class="cartx">
    <section class="cartx-checkout">
        <div class="container">
            <div class="res-wrap cartx-reveal">

                <div class="res-icon ok"><i class="fas fa-check"></i></div>
                <h2>Tu orden <span class="o">#{{ $order->reference ?? $order->slack }}</span> fue pagada exitosamente</h2>
                <p class="res-msg">Hemos enviado el comprobante a tu correo. Ya puedes empezar a estudiar.</p>

                <div class="res-card">
                    <div class="res-card-head">
                        <span class="t">Resumen de tu compra</span>
                        <span class="res-paid">
                            <i class="fas fa-circle-check"></i> Pagada
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
                            <span>Total pagado</span>
                            <b>${{ number_format($order->total_order_amount, 0, ',', '.') }} COP</b>
                        </div>
                    </div>
                </div>

                <div class="res-actions">
                    @if(auth()->check() && auth()->id() === $order->user_id)
                        <a class="primary" href="{{ route('customers.courses') }}">Ir a mis cursos</a>
                        <a class="ghost" href="{{ route('customers.orders.invoice', $order->slack) }}">Descargar recibo</a>
                    @endif
                    <a class="ghost" href="{{ route('courses') }}">Seguir explorando</a>
                </div>

            </div>
        </div>
    </section>
</main>

@push('scripts')
<script>
(function () {
    var orderId  = @json($order->slack);
    var value    = {{ (float) $order->total_order_amount }};
    var currency = 'COP';

    // Disparar la conversión una sola vez por orden (evita recargas/duplicados).
    var key = 'purchase_fired_' + orderId;
    try { if (sessionStorage.getItem(key)) return; sessionStorage.setItem(key, '1'); } catch (e) {}

    var contentIds = @json($order->items->pluck('id')->values());

    if (typeof fbq !== 'undefined') {
        fbq('track', 'Purchase', { value: value, currency: currency, content_ids: contentIds, content_type: 'product', num_items: contentIds.length });
    }
    if (typeof gtag !== 'undefined') {
        gtag('event', 'purchase', { transaction_id: orderId, value: value, currency: currency });
    }
    if (typeof ttq !== 'undefined') {
        ttq.track('CompletePayment', { value: value, currency: currency, content_type: 'product' });
    }
})();
</script>
@endpush

@endsection
