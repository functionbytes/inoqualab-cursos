{{-- A · Hermana del paquete: mismo esquema que la tarjeta de paquete
     (bundles-strip): precio antes/ahora entre separadores y botón a ancho completo. --}}
<a class="crs-card crs-card--a" href="{{ $card['url'] }}" @include('pages.partials.components.course-card._attrs')>
    <div class="crs-card-media">
        @include('pages.partials.components.course-card._image')
    </div>
    <div class="crs-card-body">
        @if ($card['category'])
            <div class="crs-card-kicker">{{ $card['category'] }}</div>
        @endif
        <h3 class="crs-card-title">{{ $card['title'] }}</h3>
        <div class="crs-card-meta">
            @include('pages.partials.components.course-card._stars')
            <span>{{ $card['lessons'] }} {{ $card['lessonsLabel'] }}</span>
            <span>{{ $card['chapters'] }} {{ $card['chaptersLabel'] }}</span>
        </div>
        <div class="crs-card-pricing">
            @if ($card['isFree'])
                <div class="crs-card-price-label">Acceso</div>
                <div class="crs-card-price-row"><span class="crs-card-price-now">Gratis</span></div>
            @elseif ($card['onSale'])
                <div class="crs-card-price-label is-was">Antes $ {{ $card['oldPrice'] }}</div>
                <div class="crs-card-price-row">
                    <span class="crs-card-price-now">$ {{ $card['price'] }}</span><small>COP</small>
                    <span class="crs-card-pill">-{{ $card['offPct'] }}%</span>
                </div>
            @else
                <div class="crs-card-price-label">Precio</div>
                <div class="crs-card-price-row"><span class="crs-card-price-now">$ {{ $card['price'] }}</span><small>COP</small></div>
            @endif
        </div>
        <span class="crs-card-cta">Ver curso <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
    </div>
</a>
