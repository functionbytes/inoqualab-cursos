{{-- C · Ficha con datos (diseño del canvas https://claude.ai/artifact/YQuNRgfE4bNYVefRT7kFnP):
     foto encajada con la categoría arriba, clases/temas/calificación en una
     mini tabla, precio antes/ahora entre separadores y botón a ancho completo. --}}
<a class="crs-card crs-card--c" href="{{ $card['url'] }}" @include('pages.partials.components.course-card._attrs')>
    <div class="crs-card-media">
        @include('pages.partials.components.course-card._image', ['withBadge' => false])
        @if ($card['category'])
            <span class="crs-card-cat">{{ $card['category'] }}</span>
        @endif
    </div>
    <div class="crs-card-body">
        <h3 class="crs-card-title">{{ $card['title'] }}</h3>

        <div class="crs-card-stats">
            <div class="crs-card-stat">
                <span class="crs-card-stat-value">{{ $card['lessons'] }}</span>
                <span class="crs-card-stat-label">{{ ucfirst($card['lessonsLabel']) }}</span>
            </div>
            <div class="crs-card-stat">
                <span class="crs-card-stat-value">{{ $card['chapters'] }}</span>
                <span class="crs-card-stat-label">{{ ucfirst($card['chaptersLabel']) }}</span>
            </div>
            <div class="crs-card-stat">
                <span class="crs-card-stat-value">
                    {{ $card['ratingText'] ?? '—' }}
                    @if ($card['ratingText'])
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.8l2.8 5.8 6.3.9-4.6 4.4 1.1 6.3L12 17.2l-5.6 3 1.1-6.3L2.9 9.5l6.3-.9z"/></svg>
                    @endif
                </span>
                <span class="crs-card-stat-label">Calificación</span>
            </div>
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
                <div class="crs-card-price-label">Pago único</div>
                <div class="crs-card-price-row"><span class="crs-card-price-now">$ {{ $card['price'] }}</span><small>COP</small></div>
            @endif
        </div>

        <span class="crs-card-cta">Ver curso <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
    </div>
</a>
