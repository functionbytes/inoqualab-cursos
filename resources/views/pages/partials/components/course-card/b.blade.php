{{-- B · Precio sobre la imagen: el precio vive sobre la foto (degradado) y
     la tarjeta queda más baja. --}}
<a class="crs-card crs-card--b" href="{{ $card['url'] }}" @include('pages.partials.components.course-card._attrs')>
    <div class="crs-card-media">
        @include('pages.partials.components.course-card._image')
        @if ($card['onSale'])
            <span class="crs-card-off">-{{ $card['offPct'] }}%</span>
        @endif
        <div class="crs-card-overlay-price">
            @if ($card['isFree'])
                <span class="crs-card-price-now">Gratis</span>
            @else
                <span class="crs-card-price-now">$ {{ $card['price'] }}</span><small>COP</small>
                @if ($card['onSale'])
                    <del>$ {{ $card['oldPrice'] }}</del>
                @endif
            @endif
        </div>
    </div>
    <div class="crs-card-body">
        @if ($card['category'])
            <div class="crs-card-kicker">{{ $card['category'] }}</div>
        @endif
        <h3 class="crs-card-title">{{ $card['title'] }}</h3>
        <div class="crs-card-meta is-split">
            @include('pages.partials.components.course-card._stars')
            <span>{{ $card['lessons'] }} {{ $card['lessonsLabel'] }} · {{ $card['chapters'] }} {{ $card['chaptersLabel'] }}</span>
        </div>
    </div>
    <div class="crs-card-foot">
        <span class="crs-card-link">Ver curso <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
    </div>
</a>
