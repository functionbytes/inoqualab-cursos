<span class="crs-card-rating">
    <span class="crs-card-stars" aria-hidden="true">
        @for ($s = 1; $s <= 5; $s++)
            <svg viewBox="0 0 24 24" fill="currentColor" class="{{ $card['rating'] > 0 && $s <= round($card['rating']) ? 'on' : '' }}"><path d="M12 2.8l2.8 5.8 6.3.9-4.6 4.4 1.1 6.3L12 17.2l-5.6 3 1.1-6.3L2.9 9.5l6.3-.9z"/></svg>
        @endfor
    </span>
    @if ($card['ratingText'])
        <b>{{ $card['ratingText'] }}</b>
    @else
        <span class="crs-card-rating-empty">Sin calificar</span>
    @endif
</span>
