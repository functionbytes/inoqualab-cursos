@php
    $drawerCart = session('cart', []);
    $drawerTotal = 0;
    $drawerCount = 0;
    foreach ($drawerCart as $ci) {
        $drawerTotal += ($ci['price'] ?? 0) * ($ci['qty'] ?? 1);
        $drawerCount += ($ci['qty'] ?? 1);
    }

    // Cupón aplicado (en sesión) y su descuento
    $drawerCouponCode = getCoupon();
    $drawerDiscount = 0;
    if ($drawerCouponCode) {
        $drawerCoupon = \App\Models\Coupon\Coupon::where('code', $drawerCouponCode)->first();
        if ($drawerCoupon) {
            [$drawerLines] = cartCheckoutLines();
            $drawerDiscount = cartCouponDiscount($drawerLines, $drawerCoupon);
        }
    }
    $drawerGrand = max(0, $drawerTotal - $drawerDiscount);
@endphp

@if(empty($drawerCart))
    <div class="cart-empty">
        <span class="ic"><i class="fas fa-cart-shopping" aria-hidden="true"></i></span>
        <h4>Tu carrito está vacío</h4>
        <p>Aún no has agregado cursos. Explora nuestro catálogo y empieza a certificarte.</p>
        <a class="cart-empty-btn" href="{{ route('courses') }}">Ver cursos</a>
    </div>
@else
    <div class="cart-body">
        @foreach($drawerCart as $key => $item)
            @php $qty = $item['qty'] ?? 1; @endphp
            @php
                $ciCompare = ! empty($item['compare']) && $item['compare'] > $item['price'];
                $ciOff = $ciCompare ? round(($item['compare'] - $item['price']) / $item['compare'] * 100) : 0;
            @endphp
            <div class="cart-item" data-key="{{ $key }}" data-unit="{{ $item['price'] }}">
                <div class="ci-main">
                    <div class="ci-tags">
                        <span class="ci-tag">{{ $item['type'] == 'bundle' ? 'Paquete' : 'Curso' }}</span>
                        @if($ciCompare)<span class="ci-off">-{{ $ciOff }}%</span>@endif
                    </div>
                    <div class="ci-name">{{ $item['title'] }}</div>
                    <div class="ci-row">
                        <div class="qty">
                            <button type="button" class="js-drawer-minus" data-key="{{ $key }}" {{ $qty <= 1 ? 'disabled' : '' }} aria-label="Quitar uno">–</button>
                            <span class="ci-qty">{{ $qty }}</span>
                            <button type="button" class="js-drawer-plus" data-key="{{ $key }}" {{ $qty >= 10 ? 'disabled' : '' }} aria-label="Agregar uno">+</button>
                        </div>
                        <span class="ci-price">
                            ${{ number_format($item['price'] * $qty, 0, ',', '.') }}
                            @if($ciCompare)<s>${{ number_format($item['compare'] * $qty, 0, ',', '.') }}</s>@endif
                        </span>
                    </div>
                </div>
                <button type="button" class="ci-remove js-drawer-remove" data-key="{{ $key }}" aria-label="Eliminar"><i class="fas fa-trash-can"></i></button>
            </div>
        @endforeach

        <div class="cart-reassure">
            <i class="fas fa-shield-halved"></i> Acceso inmediato · Certificado al finalizar
        </div>
    </div>

    <div class="cart-foot">

        @if($drawerDiscount > 0)
            {{-- Solo visualización: el cupón se aplica/quita en el checkout --}}
            <div class="cart-coupon-applied">
                <span><i class="fas fa-circle-check"></i> Cupón {{ strtoupper($drawerCouponCode) }} aplicado</span>
            </div>
        @endif

        <div class="cart-foot-row">
            <span>Subtotal</span>
            <b>${{ number_format($drawerTotal, 0, ',', '.') }} COP</b>
        </div>
        @if($drawerDiscount > 0)
            <div class="cart-foot-row cart-foot-discount">
                <span>Descuento</span>
                <b>– ${{ number_format($drawerDiscount, 0, ',', '.') }} COP</b>
            </div>
            <div class="cart-foot-row cart-foot-grand">
                <span>Total</span>
                <b>${{ number_format($drawerGrand, 0, ',', '.') }} COP</b>
            </div>
        @endif
        <p class="cart-foot-note">{{ $drawerDiscount > 0 ? 'Impuestos calculados en el pago.' : 'Cupones e impuestos se calculan en el pago.' }}</p>
        <a class="cart-view" href="{{ route('cart.index') }}">Ver carrito</a>
        <a class="cart-cta" href="{{ route('checkout.cart') }}">Finalizar compra </a>
    </div>
@endif
