@if(count($relateds) > 0)
<section class="related">
    <div class="container">
        <h2>Cursos relacionados</h2>
        <div class="related-grid">

            @foreach ($relateds as $related)
                @php
                    $relOnSale = $related->promotion == 1 && $related->discount > 0 && $related->discount < $related->price;
                    $relLessons = $related->lessons_count ?? count($related->lessons);
                    $relChapters = $related->chapters_count ?? count($related->chapters);
                @endphp
                <a class="ccard" href="{{ route('courses.view', $related->slack) }}">

                    <div class="ccard-media">
                        @if(count($related->getMedia('thumbnail')) > 0)
                            <img src="{{ $related->getFirstMedia('thumbnail')->getFullUrl() }}"
                                 alt="{{ $related->title }}" loading="lazy"
                                 onerror="this.src='{{ asset('/pages/images/courses/default.jpg') }}'">
                        @else
                            <img src="{{ asset('/pages/images/courses/default.jpg') }}"
                                 alt="{{ $related->title }}" loading="lazy">
                        @endif
                        @if($related->categorie)
                            <span class="ccard-badge premium">{{ $related->categorie->title }}</span>
                        @endif
                    </div>

                    <div class="ccard-body">
                        <div class="ccard-title">{{ $related->title }}</div>

                        <div class="ccard-rating">
                            <span class="stars">
                                @for ($s = 0; $s < 5; $s++)
                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.5l2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 17.9 6.1 20.5l1.2-6.5L2.5 9.4l6.6-.9z"/></svg>
                                @endfor
                            </span>
                            <span class="rel-price">
                                @if ($related->payment == 0)
                                    <span class="now">Gratis</span>
                                @elseif ($relOnSale)
                                    <span class="now">$ {{ number_format($related->discount) }}</span>
                                    <span class="was">$ {{ number_format($related->price) }}</span>
                                @else
                                    <span class="now">$ {{ number_format($related->price) }}</span>
                                @endif
                            </span>
                        </div>

                        <div class="ccard-meta">
                            <span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                                {{ $relLessons }} Clases
                            </span>
                            <span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                                {{ $relChapters }} Temas
                            </span>
                        </div>
                    </div>

                </a>
            @endforeach

        </div>
    </div>
</section>
@endif
