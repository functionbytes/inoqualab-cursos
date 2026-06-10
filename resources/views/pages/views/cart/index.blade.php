@extends('layouts.pages')

@section('title', 'Carrito de compras')

@section('content')
@php
    $cartTotal = 0;
    $totalQty  = 0;
    foreach ($cart as $ci) {
        $cartTotal += ($ci['price'] ?? 0) * ($ci['qty'] ?? 1);
        $totalQty  += ($ci['qty'] ?? 1);
    }
@endphp

<main class="cartx">

    {{-- Band / breadcrumb --}}
    <div class="cartx-band">
        <div class="container">
            <div class="cartx-crumb">
                INICIO <span class="sep">/</span> <span class="cur">CARRITO DE COMPRAS</span>
            </div>
            <h1>Carrito de compras</h1>
            <p class="lede">Revisa los cursos que vas a adquirir antes de finalizar tu inscripción.</p>
        </div>
    </div>

    <section class="cartx-checkout">
        <div class="container">
            <div class="cartx-grid">

                {{-- ===== Columna izquierda: items ===== --}}
                <div class="cartx-reveal">
                    <div class="cart-page-card" id="cartCard">

                        @if(empty($cart))
                            <div class="cp-empty">
                                <div class="cp-empty-icon"><i class="fas fa-cart-shopping"></i></div>
                                <h3>Tu carrito está vacío</h3>
                                <p>Aún no has agregado cursos ni paquetes. Explora el catálogo y empieza a certificarte.</p>
                                <div class="cp-empty-actions">
                                    <a class="btn-solid" href="{{ route('courses') }}">
                                        <i class="fas fa-graduation-cap"></i> Ver cursos
                                    </a>
                                    <a class="btn-outline-dark" href="{{ route('bundles') }}">
                                        <i class="fas fa-layer-group"></i> Ver paquetes
                                    </a>
                                </div>
                            </div>
                        @else
                            <h2 class="cp-title">
                                <span id="cpCount">{{ count($cart) }}</span>
                                <span id="cpCountWord">{{ count($cart) == 1 ? 'producto' : 'productos' }}</span> en tu carrito
                            </h2>
                            <p class="cp-sub">Revisa tu selección antes de continuar al pago.</p>

                            <div class="cp-head-row">
                                <span>Producto</span>
                                <span class="c-qty">Cantidad</span>
                                <span class="c-price">Precio</span>
                                <span></span>
                            </div>

                            @foreach($cart as $key => $item)
                            @php
                                $qty = $item['qty'] ?? 1;
                                $cpCompare = ! empty($item['compare']) && $item['compare'] > $item['price'];
                                $cpOff = $cpCompare ? round(($item['compare'] - $item['price']) / $item['compare'] * 100) : 0;
                            @endphp
                            <div class="cp-item" data-key="{{ $key }}" data-unit="{{ $item['price'] }}">
                                <div class="cp-prod">
                                    @if(!empty($item['image']))
                                        <div class="cp-thumb">
                                            <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}"
                                                 onerror="this.style.display='none'">
                                        </div>
                                    @endif
                                    <div class="cp-info">
                                        <div class="cp-tags">
                                            <span class="cp-tag">{{ $item['type'] == 'bundle' ? 'Paquete' : 'Curso' }}</span>
                                            @if($cpCompare)<span class="cp-off">-{{ $cpOff }}%</span>@endif
                                        </div>
                                        <div class="cp-name">
                                            {{ $item['title'] }}
                                            <small>Modalidad virtual · Certificado incluido</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="cp-qty-cell">
                                    <div class="qty">
                                        <button type="button" class="js-qty-minus" data-key="{{ $key }}" {{ $qty <= 1 ? 'disabled' : '' }} aria-label="Quitar uno">–</button>
                                        <span class="qty-val">{{ $qty }}</span>
                                        <button type="button" class="js-qty-plus" data-key="{{ $key }}" {{ $qty >= 10 ? 'disabled' : '' }} aria-label="Agregar uno">+</button>
                                    </div>
                                </div>
                                <div class="cp-price-cell">
                                    <div class="amt">$ {{ number_format($item['price'] * $qty, 0, ',', '.') }}</div>
                                    @if($cpCompare)<span class="cp-compare">$ {{ number_format($item['compare'] * $qty, 0, ',', '.') }}</span>@endif
                                    <span class="unit {{ $qty > 1 ? '' : 'is-hidden' }}">$ {{ number_format($item['price'], 0, ',', '.') }} c/u</span>
                                </div>
                                <button type="button" class="cp-remove js-cart-remove" data-key="{{ $key }}" aria-label="Eliminar">
                                    <i class="fas fa-trash-can"></i>
                                </button>
                            </div>
                            @endforeach

                            <div class="cp-actions">
                                <button type="button" class="btn-ghost danger js-cart-clear">
                                    <i class="fas fa-trash-can"></i> Vaciar carrito
                                </button>
                                <a class="btn-ghost" href="{{ route('courses') }}">
                                    <i class="fas fa-chevron-left"></i> Seguir comprando
                                </a>
                            </div>
                        @endif

                    </div>
                </div>

                {{-- ===== Columna derecha: resumen ===== --}}
                <div class="cartx-summary-col cartx-reveal">
                    <div class="summary">
                        <div class="summary-head">
                            <div class="eyebrow">Detalle de la orden</div>
                            <h3>Resumen orden</h3>
                        </div>
                        <div class="summary-body" id="summaryBody">

                            @if(empty($cart))
                                <div class="cartx-sum-empty">
                                    <p>Agrega un curso para ver el resumen de tu compra.</p>
                                </div>
                            @else
                                <div class="totals">
                                    <div class="trow">
                                        <span class="lbl">Subtotal<span id="sumQty">{{ $totalQty > 1 ? ' ('.$totalQty.')' : '' }}</span></span>
                                        <span class="val" id="sumSubtotal">$ {{ number_format($cartTotal, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="trow">
                                        <span class="lbl">Envío</span>
                                        <span class="val cartx-free">Gratis</span>
                                    </div>
                                    <div class="totals-divider"></div>
                                    <div class="trow total">
                                        <span class="lbl">Total</span>
                                        <span class="val" id="sumTotal">$ {{ number_format($cartTotal, 0, ',', '.') }}</span>
                                    </div>
                                </div>

                                <div class="coupon-note">
                                    <i class="fas fa-tag"></i> ¿Tienes un cupón? Aplícalo en el siguiente paso, durante el pago.
                                </div>

                                <a class="go-pay" id="goPayBtn" href="{{ route('checkout.cart') }}">
                                    <i class="fas fa-lock" id="goPayIcon"></i> <span id="goPayText">Ir al pago</span> <span class="arr">→</span>
                                </a>

                                <div class="trust">
                                    <i class="fas fa-shield-halved"></i> Pago 100% seguro procesado con Wompi
                                </div>

                                @if(setting('page_cellphone'))
                                <div class="summary-foot">
                                    <span class="lbl">Para más detalles</span>
                                    <a class="phone" href="tel:{{ setting('page_cellphone') }}">
                                        <i class="fas fa-phone"></i> {{ setting('page_cellphone') }}
                                    </a>
                                </div>
                                @endif
                            @endif

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

</main>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    function fmtCOP(n) {
        return '$ ' + Number(n).toLocaleString('es-CO');
    }

    function updateHeaderBadge(count) {
        var $badge = $('.cart-count-badge');
        if (count > 0) {
            if ($badge.length) {
                $badge.text(count);
            } else {
                $('.cart-btn').append('<span class="cart-count-badge">' + count + '</span>');
            }
        } else {
            $badge.remove();
        }
    }

    // Actualizar cantidad
    function changeQty(key, delta) {
        var $row  = $('.cp-item[data-key="' + key + '"]');
        var $val  = $row.find('.qty-val');
        var $minus = $row.find('.js-qty-minus');
        var $plus  = $row.find('.js-qty-plus');
        var unit  = parseFloat($row.data('unit')) || 0;
        var current = parseInt($val.text(), 10) || 1;
        var next  = Math.max(1, Math.min(10, current + delta));

        if (next === current) return;

        $minus.add($plus).prop('disabled', true);

        $.ajax({
            url: '{{ route('cart.update-qty') }}',
            method: 'POST',
            data: { key: key, qty: next },
            success: function (res) {
                $val.text(res.qty);
                $minus.prop('disabled', res.qty <= 1);
                $plus.prop('disabled', res.qty >= 10);

                // Precio de la línea + precio unitario
                $row.find('.cp-price-cell .amt').text(fmtCOP(unit * res.qty));
                var $unit = $row.find('.cp-price-cell .unit');
                if (res.qty > 1) {
                    $unit.text(fmtCOP(unit) + ' c/u').removeClass('is-hidden');
                } else {
                    $unit.addClass('is-hidden');
                }

                // Totales del resumen
                $('#sumSubtotal').text(fmtCOP(res.cart_total));
                $('#sumTotal').text(fmtCOP(res.cart_total));

                // Contador de unidades en el subtotal
                var totalQty = 0;
                $('.qty-val').each(function () { totalQty += parseInt($(this).text(), 10) || 0; });
                $('#sumQty').text(totalQty > 1 ? ' (' + totalQty + ')' : '');
            },
            error: function () {
                $minus.prop('disabled', current <= 1);
                $plus.prop('disabled', current >= 10);
                if (typeof toastr !== 'undefined') toastr.error('No se pudo actualizar la cantidad.');
            }
        });
    }

    $(document).on('click', '.js-qty-plus', function () { changeQty($(this).data('key'), 1); });
    $(document).on('click', '.js-qty-minus', function () { changeQty($(this).data('key'), -1); });

    // Eliminar un item
    $(document).on('click', '.js-cart-remove', function () {
        var $btn = $(this);
        var key  = $btn.data('key');
        var $row = $('.cp-item[data-key="' + key + '"]');

        $btn.prop('disabled', true);

        $.ajax({
            url: '{{ route('cart.remove') }}',
            method: 'POST',
            data: { key: key },
            success: function (res) {
                updateHeaderBadge(res.cart_count);

                // Si queda vacío o un solo item, recargamos para reflejar
                // estados (vacío / botón "Ir al pago") renderizados en servidor.
                if (res.cart_count <= 1) {
                    window.location.reload();
                    return;
                }

                $row.addClass('removing');
                setTimeout(function () {
                    $row.remove();
                    $('#cpCount').text(res.cart_count);
                    $('#cpCountWord').text(res.cart_count === 1 ? 'producto' : 'productos');
                    $('#sumSubtotal').text(fmtCOP(res.cart_total));
                    $('#sumTotal').text(fmtCOP(res.cart_total));
                    if (typeof toastr !== 'undefined') toastr.success(res.message);
                }, 260);
            },
            error: function () {
                $btn.prop('disabled', false);
                if (typeof toastr !== 'undefined') toastr.error('No se pudo eliminar el producto.');
            }
        });
    });

    // Vaciar carrito
    $(document).on('click', '.js-cart-clear', function () {
        var $btn = $(this);
        $btn.prop('disabled', true);

        $.ajax({
            url: '{{ route('cart.clear') }}',
            method: 'POST',
            success: function (res) {
                updateHeaderBadge(0);
                window.location.reload();
            },
            error: function () {
                $btn.prop('disabled', false);
                if (typeof toastr !== 'undefined') toastr.error('No se pudo vaciar el carrito.');
            }
        });
    });
})();
</script>
@endpush
