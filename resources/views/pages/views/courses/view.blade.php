@extends('layouts.pages')

@inject('finds', 'App\Models\Course\Course')

@section('title', "$course->title")

@section('head')

    @php
    $url = URL::current();
    @endphp

    <meta name="title" content="{{ $course->title }}">
    <meta name="description" content="{{ $course->short_detail }} ">
    <meta property="og:title" content="{{ $course->title }} ">
    <meta property="og:url" content="{{ $url }}">
    <meta property="og:description" content="{{ $course->short_detail }}">
    <meta property="og:image" content="{{ asset('images/course/' . $course->preview_image) }}">
    <meta itemprop="image" content="{{ asset('images/course/' . $course->preview_image) }}">
    <meta property="og:type" content="website">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ asset('images/course/' . $course->preview_image) }}">
    <meta property="twitter:title" content="{{ $course->title }} ">
    <meta property="twitter:description" content="{{ $course->short_detail }}">
    <meta name="twitter:site" content="{{ url()->full() }}" />

    <link rel="canonical" href="{{ url()->full() }}" />
    <meta name="robots" content="all">
    <meta name="keywords" content="">

@endsection

@section('content')

@php
    $hasThumb = count($course->getMedia('thumbnail')) > 0;
    $thumbUrl = $hasThumb ? $course->getfirstMedia('thumbnail')->getfullUrl() : asset('/pages/images/courses/default.jpg');
    $lessonsCount = count($leasons);
    $chaptersCount = count($chapters);
@endphp

<main class="cartx">

    {{-- Hero --}}
    <section class="course-hero">
        <div class="container">
            <div class="ch-eyebrow">{{ $course->categorie?->title }}</div>
            <h1>{{ $course->title }}</h1>
            @php $rating = (float) ($course->rating ?? 0); @endphp
            @if ($rating > 0)
                <div class="ch-rating">
                    <span class="ch-stars">
                        @for ($s = 1; $s <= 5; $s++)
                            <i class="{{ $s <= round($rating) ? 'fas' : 'far' }} fa-star"></i>
                        @endfor
                    </span>
                    <span class="score">{{ number_format($rating, 1) }}</span>
                    <span class="reviews">(Curso certificado)</span>
                </div>
            @endif
            <div class="ch-meta">
                <span><i class="fa-solid fa-clock"></i> {{ $course->duration }} {{ $course->duration == 1 ? 'hora' : 'horas' }} de contenido</span>
                <span><i class="fa-solid fa-circle-play"></i> {{ $lessonsCount }} clases</span>
                @if($course->certificate == 1)<span><i class="fa-solid fa-award"></i> Certificado al finalizar</span>@endif
                <span><i class="fa-solid fa-user"></i> Modalidad 100% virtual</span>
            </div>
        </div>
    </section>

    {{-- Body: contenido + buy card --}}
    <section class="course-body">
        <div class="container">
            <div class="course-grid">

                <div class="course-content cartx-reveal">
                    @if ($course->short != null)
                        <div class="cs-block">
                            <h2>De qué trata este curso</h2>
                            {!! $course->short !!}
                        </div>
                    @endif

                    @if ($course->learn != null)
                        <div class="cs-rule"></div>
                        <div class="cs-block">
                            <h2>¿Qué aprenderás?</h2>
                            <div class="cs-learn">{!! $course->learn !!}</div>
                        </div>
                    @endif
                </div>

                {{-- Buy card --}}
                <aside class="buy-card cartx-reveal">
                    @php $courseOnSale = $course->payment == 1 && $course->promotion == 1 && $course->discount < $course->price; @endphp
                    <div class="buy-media">
                        <img src="{{ $thumbUrl }}" alt="{{ $course->title }}" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('/pages/images/courses/default.jpg') }}';">
                        @if ($courseOnSale)
                            <span class="buy-off">-{{ round(($course->price - $course->discount) / $course->price * 100) }}% OFF</span>
                        @endif
                        @if ($course->film != null)
                            <a href="{{ $course->film }}" class="play-badge popup-video"><i class="fas fa-circle-play"></i> Vista previa</a>
                        @endif
                    </div>
                    <div class="buy-pad">

                        <div class="buy-price">
                            @if ($course->payment == 1)
                                @if ($course->promotion == 1)
                                    <span class="amt">${{ number_format($course->discount) }} <small>${{ number_format($course->price) }}</small></span>
                                @else
                                    <span class="amt">${{ number_format($course->price) }}</span>
                                @endif
                                <span class="cur">COP</span>
                                <span class="note">Acceso de por vida</span>
                            @else
                                <span class="amt">GRATIS</span>
                                <span class="note">Acceso inmediato</span>
                            @endif
                        </div>

                        <div class="buy-details">
                            <div class="row"><span class="ic"><i class="fa-solid fa-clock"></i></span><span class="lbl">Duración</span><span class="val">{{ $course->duration }} {{ $course->duration == 1 ? 'hora' : 'horas' }}</span></div>
                            <div class="row"><span class="ic"><i class="fa-solid fa-bolt"></i></span><span class="lbl">Categoría</span><span class="val">{{ Str::limit(Str::ucfirst(Str::lower($course->categorie?->title ?? '')), 18) }}</span></div>
                            <div class="row"><span class="ic"><i class="fa-solid fa-circle-play"></i></span><span class="lbl">Clases</span><span class="val">{{ $lessonsCount }}</span></div>
                            <div class="row"><span class="ic"><i class="fa-solid fa-folder"></i></span><span class="lbl">Temas</span><span class="val">{{ $chaptersCount }}</span></div>
                            <div class="row"><span class="ic"><i class="fa-solid fa-list-check"></i></span><span class="lbl">Examen</span><span class="val">{{ $course->exam == 1 ? 'Sí' : 'No' }}</span></div>
                            <div class="row"><span class="ic"><i class="fa-solid fa-award"></i></span><span class="lbl">Certificado</span><span class="val">{{ $course->certificate == 1 ? 'Sí' : 'No' }}</span></div>
                        </div>

                        @if ($alreadyOwned)
                            <div class="buy-actions">
                                @if ($inscriptionSlack)
                                    <a href="{{ route('customers.courses.content', $inscriptionSlack) }}" class="buy-primary"><i class="fa-solid fa-play"></i> Continuar aprendiendo</a>
                                @else
                                    <a href="{{ route('customers.courses') }}" class="buy-primary"><i class="fa-solid fa-play"></i> Ir a mis cursos</a>
                                @endif
                                <span class="buy-owned-note"><i class="fa-solid fa-circle-check"></i> Ya adquiriste este curso</span>
                            </div>
                        @elseif ($course->payment == 1)
                            <form class="form-add-to-cart" action="{{ route('cart.add') }}" method="POST">
                                @csrf
                                <input type="hidden" name="type" value="course">
                                <input type="hidden" name="slack" value="{{ $course->slack }}">
                                <input type="hidden" name="qty" id="buyQty" value="1">
                                <input type="hidden" name="buy_now" id="buyNow" value="">

                                <div class="buy-qty">
                                    <span class="lbl">Cantidad</span>
                                    <div class="qty">
                                        <button type="button" class="js-buy-minus" aria-label="Quitar uno" disabled>–</button>
                                        <span id="buyQtyVal">1</span>
                                        <button type="button" class="js-buy-plus" aria-label="Agregar uno">+</button>
                                    </div>
                                </div>

                                <div class="buy-actions">
                                    <button type="submit" class="buy-primary" id="btnBuyNow"><i class="fa-solid fa-bolt"></i> Compra ahora</button>
                                    <button type="submit" class="buy-outline" id="btnAddCart"><i class="fa-solid fa-cart-plus"></i> Agregar al carrito</button>
                                </div>
                            </form>
                        @else
                            <div class="buy-actions">
                                <a href="{{ route('checkout', ['course', $course->slack]) }}" class="buy-primary"><i class="fa-solid fa-bolt"></i> Obtener gratis</a>
                            </div>
                        @endif

                        @if(setting('whatsapp'))
                        <div class="buy-foot">
                            <span class="lbl">Para más detalles</span>
                            <a class="phone" href="tel:{{ setting('whatsapp') }}"><i class="fa-solid fa-phone"></i> {{ setting('whatsapp') }}</a>
                        </div>
                        @endif

                    </div>
                </aside>

            </div>
        </div>
    </section>

    {{-- Contenido del curso + certificador --}}
    <section class="course-extra">
        <div class="container">

            <div class="cur-head">
                <h2>Contenido del curso</h2>
                <div class="cur-stats">
                    <div class="stat"><div class="n">{{ $chaptersCount }}</div><div class="l">Temas</div></div>
                    <div class="stat"><div class="n">{{ $lessonsCount }}</div><div class="l">Clases</div></div>
                    <div class="stat"><div class="n">{{ $course->exam == 1 ? 'Sí' : 'No' }}</div><div class="l">Examen</div></div>
                </div>
            </div>

            @if ($course->chapters->isNotEmpty())
                <div class="accordion">
                    @foreach ($course->chapters as $i => $chapter)
                        @php $isFirst = $loop->first; @endphp
                        <div class="acc-item">
                            <button type="button" class="acc-head js-acc {{ $isFirst ? 'open' : '' }}">
                                <span class="idx">{{ $i + 1 }}</span>
                                <span class="ttl">{{ ucfirst($chapter->title) }}</span>
                                <span class="meta">{{ $chapter->lessons->count() }} clases</span>
                                <span class="chev"><i class="fas fa-chevron-down"></i></span>
                            </button>
                            <div class="acc-body" style="{{ $isFirst ? '' : 'display:none;' }}">
                                @foreach ($chapter->lessons as $k => $lesson)
                                    @if ($lesson->available == 1)
                                        <div class="lesson">
                                            <span class="num">{{ str_pad($k + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                            <span class="pl"><i class="fa-solid fa-circle-play"></i></span>
                                            {{ ucfirst($lesson->title) }}
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Certificador --}}
            <div class="certifier">
                <h2>Certificador</h2>
                <div class="cert-card">
                    <div class="cert-left">
                        <div class="cert-avatar">
                            @if($course->certifier->hasMedia('thumbnail'))
                                <img src="{{ $course->certifier->getFirstMediaUrl('thumbnail') }}"
                                     alt="{{ $course->certifier->firstname }}" loading="lazy"
                                     onerror="this.style.display='none';this.nextElementSibling.style.display='';">
                                <i class="fa-solid fa-user" style="display:none;"></i>
                            @else
                                <i class="fa-solid fa-user"></i>
                            @endif
                        </div>
                    </div>
                    <div class="cert-info">
                        <div class="name">{{ $course->certifier->firstname }} {{ $course->certifier->lastname }}</div>
                        <span class="role">{{ $course->certifier->profession }}</span>
                        @if($course->certifier->description != null)
                            <div class="info">{!! $course->certifier->description !!}</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Opiniones de estudiantes (solo si el administrador habilitó las reseñas) --}}
            @if (setting('reviews_enabled') && isset($reviews) && $reviews->isNotEmpty())
                <div class="reviews-section">
                    <div class="rev-head">
                        <h2>Opiniones de estudiantes</h2>
                        <div class="rev-summary">
                            <span class="rev-avg">{{ number_format($course->rating, 1) }}</span>
                            <span class="rev-stars">
                                @for ($s = 1; $s <= 5; $s++)
                                    <i class="{{ $s <= round($course->rating) ? 'fas' : 'far' }} fa-star"></i>
                                @endfor
                            </span>
                            <span class="rev-count">{{ $reviewsCount }} {{ $reviewsCount == 1 ? 'reseña' : 'reseñas' }}</span>
                        </div>
                    </div>
                    <div class="rev-grid">
                        @foreach ($reviews as $review)
                            @php
                                $fn = $review->user->firstname ?? 'Estudiante';
                                $ln = $review->user->lastname ?? '';
                            @endphp
                            <div class="rev-card">
                                <div class="rev-card-top">
                                    <div class="rev-avatar">{{ Str::upper(Str::substr($fn, 0, 1)) }}</div>
                                    <div class="rev-meta">
                                        <span class="rev-name">{{ ucfirst(Str::lower($fn)) }} {{ $ln ? ucfirst(Str::lower(Str::substr($ln, 0, 1))) . '.' : '' }}</span>
                                        <span class="rev-stars sm">
                                            @for ($s = 1; $s <= 5; $s++)
                                                <i class="{{ $s <= $review->rating ? 'fas' : 'far' }} fa-star"></i>
                                            @endfor
                                        </span>
                                    </div>
                                    <span class="rev-date">{{ $review->created_at?->format('d/m/Y') }}</span>
                                </div>
                                <p class="rev-comment">{{ $review->comment }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </section>

    @include ('pages.partials.sections.courses.related')

</main>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        // Acordeón de contenido
        $(document).on('click', '.js-acc', function () {
            var $head = $(this);
            var $body = $head.next('.acc-body');
            var isOpen = $head.hasClass('open');
            $('.js-acc').removeClass('open');
            $('.acc-body').slideUp(180);
            if (!isOpen) {
                $head.addClass('open');
                $body.slideDown(180);
            }
        });

        // Stepper de cantidad del buy-card
        function setBuyQty(q) {
            q = Math.max(1, Math.min(10, q));
            $('#buyQty').val(q);
            $('#buyQtyVal').text(q);
            $('.js-buy-minus').prop('disabled', q <= 1);
            $('.js-buy-plus').prop('disabled', q >= 10);
        }
        $(document).on('click', '.js-buy-plus', function () { setBuyQty((parseInt($('#buyQty').val(), 10) || 1) + 1); });
        $(document).on('click', '.js-buy-minus', function () { setBuyQty((parseInt($('#buyQty').val(), 10) || 1) - 1); });

        // Diferenciar "Compra ahora" vs "Agregar al carrito"
        $(document).on('click', '#btnBuyNow', function () { $('#buyNow').val('1'); });
        $(document).on('click', '#btnAddCart', function () { $('#buyNow').val(''); });
    });
</script>
@endpush

@push('css')
<style>
    .reviews-section { margin-top: 54px; }
    .rev-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 22px; }
    .rev-head h2 { font-size: 24px; font-weight: 800; letter-spacing: -.01em; margin: 0; color: #1b2a3a; }
    .rev-summary { display: inline-flex; align-items: center; gap: 10px; }
    .rev-summary .rev-avg { font-size: 26px; font-weight: 800; color: #0d1b2a; }
    .rev-stars { display: inline-flex; gap: 2px; color: #f5b740; font-size: 16px; }
    .rev-stars.sm { font-size: 13px; }
    .rev-stars .far { color: #d8dee5; }
    .rev-summary .rev-count { font-size: 13px; color: #6a7888; font-weight: 600; }
    .buy-owned-note { display: block; text-align: center; font-size: 13px; font-weight: 600; color: #008bcd; margin-top: 8px; }
    .buy-owned-note i { margin-right: 4px; }
    .rev-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 18px; }
    .rev-card { background: #fff; border: 1px solid #e7ecf1; border-radius: 14px; padding: 20px 22px; box-shadow: 0 6px 22px rgba(13,27,42,.06); }
    .rev-card-top { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
    .rev-avatar { width: 42px; height: 42px; flex: 0 0 auto; border-radius: 50%; background: linear-gradient(150deg, #0d1b2a, #16304a); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 16px; }
    .rev-meta { display: flex; flex-direction: column; gap: 3px; flex: 1; min-width: 0; }
    .rev-name { font-size: 14.5px; font-weight: 700; color: #1b2a3a; }
    .rev-date { font-size: 12px; color: #93a0ad; font-weight: 600; white-space: nowrap; }
    .rev-comment { font-size: 14.5px; line-height: 1.6; color: #46586a; margin: 0; }
    @media (max-width: 720px) {
        .rev-grid { grid-template-columns: 1fr; }
        .rev-head { flex-direction: column; align-items: flex-start; }
    }
</style>
@endpush
