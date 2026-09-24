{{-- D · Base oscura corporativa: tarjeta blanca con una franja azul marino
     al pie que lleva el precio y la llamada a la acción. --}}
<a class="crs-card crs-card--d" href="{{ $card['url'] }}" @include('pages.partials.components.course-card._attrs')>
    <div class="crs-card-media">
        @include('pages.partials.components.course-card._image')
    </div>
    <div class="crs-card-body">
        @if ($card['category'])
            <div class="crs-card-kicker">{{ $card['category'] }}</div>
        @endif
        <h3 class="crs-card-title">{{ $card['title'] }}</h3>
        <div class="crs-card-meta is-split">
            @include('pages.partials.components.course-card._stars')
            <span class="crs-card-chips">
                <span>{{ $card['lessons'] }} {{ $card['lessonsLabel'] }}</span>
                <span>{{ $card['chapters'] }} {{ $card['chaptersLabel'] }}</span>
            </span>
        </div>
    </div>
    <div class="crs-card-band">
        <div>
            @if ($card['isFree'])
                <div class="crs-card-price-label">Acceso</div>
                <div class="crs-card-price-now">Gratis</div>
            @elseif ($card['onSale'])
                <div class="crs-card-price-label"><del>$ {{ $card['oldPrice'] }}</del> <b>-{{ $card['offPct'] }}%</b></div>
                <div class="crs-card-price-now">$ {{ $card['price'] }} <small>COP</small></div>
            @else
                <div class="crs-card-price-label">Precio</div>
                <div class="crs-card-price-now">$ {{ $card['price'] }} <small>COP</small></div>
            @endif
        </div>
        <span class="crs-card-band-cta"><span class="crs-card-band-text">Ver curso</span><span class="crs-card-band-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span></span>
    </div>
</a>
