@extends('layouts.pages')

@section('active', 'courses')

@section('title', $course->title)

@push('css')
<link rel="stylesheet" href="{{ url('/pages/css/storefront.css') }}?v={{ @filemtime(public_path('pages/css/storefront.css')) ?: '1' }}">
<style>
    /* ── buy-card flota dentro del hero (como bundles/paquete) ─────── */
    .dc-page .course-hero .container { padding-bottom: 96px; }
    .dc-page .buy-card { top: 96px; margin-top: -250px; margin-bottom: 64px; max-height: calc(100vh - 120px); overflow-y: auto; }
    .dc-page .buy-card::-webkit-scrollbar { width: 0; }
    @media (max-width: 920px) { .dc-page .buy-card { margin-top: -150px; } }

    /* ── Iconos Font Awesome dentro de componentes (storefront.css solo dimensiona <svg>) ─ */
    .ch-meta i { color: var(--green); font-size: 15px; }
    .buy-details .row .ic i { font-size: 14px; }
    .buy-primary i { color: var(--green); font-size: 16px; }
    .buy-outline i { font-size: 16px; }
    .buy-foot .phone i { color: var(--green-deep); font-size: 14px; }
    .acc-head .chev i { font-size: 16px; }
    .lesson .pl i { font-size: 14px; }
    .cur-expand i { font-size: 13px; }
    .cert-avatar i { font-size: 38px; color: var(--green); }

    /* ── Insignia de descuento sobre la imagen del buy-card ───────── */
    .buy-media .buy-off { position: absolute; top: 12px; right: 12px; z-index: 2; background: var(--green); color: #fff; font-size: 11.5px; font-weight: 800; letter-spacing: .04em; padding: 6px 11px; border-radius: 8px; box-shadow: 0 6px 16px rgba(0,139,205,.4); }

    /* ── buy-was: precio anterior tachado ─────────────────────────── */
    .buy-was { font-size: 13px; color: var(--muted); font-weight: 600; margin-top: 8px; }
    .buy-was s { color: var(--muted-2); }

    /* ── Nota de curso ya adquirido ───────────────────────────────── */
    .buy-owned-note { display: block; text-align: center; font-size: 13px; font-weight: 600; color: var(--green); margin-top: 12px; }
    .buy-owned-note i { margin-right: 4px; }

    /* ── ¿Qué aprenderás? lista con checks Font Awesome ───────────── */
    .cs-learn ul { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 28px; margin: 6px 0 0; padding: 0; list-style: none; }
    .cs-learn ul li { display: flex; gap: 11px; font-size: 14.5px; line-height: 1.5; color: #3c4d5e; }
    .cs-learn ul li::before { content: "\f00c"; font-family: "Font Awesome 6 Free"; font-weight: 900; width: 22px; height: 22px; border-radius: 7px; background: var(--green-soft); color: var(--green-deep); display: inline-flex; align-items: center; justify-content: center; flex: 0 0 auto; font-size: 11px; margin-top: 1px; }
    .cs-learn p { color: #46586a; font-size: 15px; line-height: 1.75; margin: 0 0 14px; }
    @media (max-width: 720px) { .cs-learn ul { grid-template-columns: 1fr; } }

    /* ── Reviews ──────────────────────────────────────────────────── */
    .reviews-section { margin-top: 54px; }
    .rev-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 22px; }
    .rev-head h2 { font-size: 24px; font-weight: 800; letter-spacing: -.01em; margin: 0; color: var(--ink); }
    .rev-summary { display: inline-flex; align-items: center; gap: 10px; }
    .rev-summary .rev-avg { font-size: 26px; font-weight: 800; color: var(--navy); }
    .rev-stars { display: inline-flex; gap: 2px; color: #f5b740; font-size: 16px; }
    .rev-stars.sm { font-size: 13px; }
    .rev-stars .far { color: #d8dee5; }
    .rev-summary .rev-count { font-size: 13px; color: var(--muted); font-weight: 600; }
    .rev-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 18px; }
    .rev-card { background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 20px 22px; box-shadow: var(--shadow-soft); }
    .rev-card-top { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
    .rev-avatar { width: 42px; height: 42px; flex: 0 0 auto; border-radius: 50%; background: linear-gradient(150deg, var(--navy), var(--navy-soft)); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 16px; }
    .rev-meta { display: flex; flex-direction: column; gap: 3px; flex: 1; min-width: 0; }
    .rev-name { font-size: 14.5px; font-weight: 700; color: var(--ink); }
    .rev-date { font-size: 12px; color: var(--muted-2); font-weight: 600; white-space: nowrap; }
    .rev-comment { font-size: 14.5px; line-height: 1.6; color: #46586a; margin: 0; }
    @media (max-width: 720px) {
        .rev-grid { grid-template-columns: 1fr; }
        .rev-head { flex-direction: column; align-items: flex-start; }
    }

    /* ── Cursos relacionados (la partial usa .related/.ccard sin scope) ─ */
    .related { background: #f3f6f9; padding: 60px 0 72px; margin-top: 54px; }
    .related h2 { text-align: center; font-size: 28px; font-weight: 800; letter-spacing: -.01em; text-transform: uppercase; margin: 0 0 36px; color: var(--ink); }
    .related-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 26px; max-width: 1100px; margin: 0 auto; }
    @media (max-width: 900px) { .related-grid { grid-template-columns: 1fr; max-width: 380px; } }
    .ccard { background: #fff; border: 1px solid var(--line); border-radius: 16px; overflow: hidden; box-shadow: var(--shadow-soft); transition: transform .25s var(--ease), box-shadow .25s var(--ease), border-color .25s var(--ease); display: flex; flex-direction: column; text-decoration: none; }
    .ccard:hover { transform: translateY(-6px); box-shadow: 0 18px 44px rgba(13,27,42,.14); border-color: var(--green); }
    .ccard-media { position: relative; aspect-ratio: 16/10; background: linear-gradient(150deg, var(--navy), var(--navy-soft)); overflow: hidden; }
    .ccard-media img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .ccard-badge { position: absolute; top: 12px; left: 12px; z-index: 2; font-size: 10.5px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; padding: 5px 10px; border-radius: 6px; }
    .ccard-badge.premium { background: rgba(13,27,42,.82); color: #fff; backdrop-filter: blur(4px); }
    .ccard-badge.free { background: var(--green); color: #fff; }
    .ccard-body { padding: 16px 18px 18px; display: flex; flex-direction: column; flex: 1; }
    .ccard-title { font-size: 14.5px; font-weight: 800; line-height: 1.35; color: var(--ink); margin: 0; letter-spacing: .01em; }
    .ccard-rating { display: flex; align-items: center; justify-content: space-between; gap: 7px; margin: 10px 0 0; }
    .ccard-rating .stars { display: inline-flex; gap: 2px; color: #f5b740; }
    .ccard-rating .stars svg { width: 14px; height: 14px; }
    .ccard-meta { display: flex; gap: 14px; margin: 12px 0 0; color: var(--muted); font-size: 12.5px; font-weight: 600; }
    .ccard-meta span { display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; }
    .ccard-meta svg { width: 14px; height: 14px; color: var(--green-deep); }
    .rel-price { display: flex; align-items: baseline; gap: 8px; }
    .rel-price .now { font-size: 17px; font-weight: 800; color: var(--navy); white-space: nowrap; }
    .rel-price .was { font-size: 13px; color: var(--muted-2); text-decoration: line-through; }
</style>
@endpush

@section('content')

@php
    $hasThumb = count($course->getMedia('thumbnail')) > 0;
    $thumbUrl = $hasThumb ? $course->getFirstMedia('thumbnail')->getFullUrl() : asset('/pages/images/courses/default.jpg');
    $lessonsCount = count($leasons);
    $chaptersCount = count($chapters);
    $courseOnSale = $course->payment == 1 && $course->promotion == 1 && $course->discount < $course->price;
    $discountPct = $courseOnSale ? round(($course->price - $course->discount) / $course->price * 100) : 0;
    $rating = (float) ($course->rating ?? 0);
@endphp

<div class="dc-page">

    {{-- ===== Hero (navy) ===== --}}
    <section class="course-hero">
        <div class="container">
            <div class="ch-eyebrow">{{ $course->categorie?->title }}</div>
            <h1>{{ $course->title }}</h1>
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
                @if ($course->certificate == 1)<span><i class="fa-solid fa-award"></i> Certificado al finalizar</span>@endif
                <span><i class="fa-solid fa-laptop"></i> Modalidad 100% virtual</span>
            </div>
        </div>
    </section>

    {{-- ===== Cuerpo: contenido + buy card flotante ===== --}}
    <section class="course-body">
        <div class="container">
            <div class="course-grid">

                <div class="course-content">

                    {{-- De qué trata --}}
                    @if ($course->short != null)
                        <div class="cs-block">
                            <h2>De qué trata este curso</h2>
                            {!! clean($course->short, 'content') !!}
                        </div>
                    @endif

                    {{-- Qué aprenderás --}}
                    @if ($course->learn != null)
                        <div class="cs-rule"></div>
                        <div class="cs-block">
                            <h2>¿Qué aprenderás?</h2>
                            <div class="cs-learn">{!! clean($course->learn, 'content') !!}</div>
                        </div>
                    @endif

                    {{-- Contenido del curso --}}
                    <div class="cs-rule"></div>

                    <div class="cur-head">
                        <div class="cur-head-top">
                            <h2>Contenido del curso</h2>
                        </div>
                        <p class="lead">{{ $chaptersCount }} {{ $chaptersCount == 1 ? 'módulo' : 'módulos' }} con videos, lecturas y evaluaciones. Avanza a tu ritmo y obtén tu certificado al finalizar.</p>
                    </div>

                    <div class="cur-acc-actions">
                        <span class="info"><b>{{ $chaptersCount }} {{ $chaptersCount == 1 ? 'módulo' : 'módulos' }}</b> · {{ $lessonsCount }} clases · {{ $course->duration }} horas de contenido</span>
                        <button type="button" class="cur-expand" id="expandAll">
                            <i class="fa-solid fa-angles-down"></i> Expandir todo
                        </button>
                    </div>

                    @if ($course->chapters->isNotEmpty())
                        <div class="accordion" id="accordion">
                            @foreach ($course->chapters as $i => $chapter)
                                @php $isFirst = $loop->first; @endphp
                                <div class="acc-item">
                                    <button type="button" class="acc-head js-acc {{ $isFirst ? 'open' : '' }}" data-acc>
                                        <span class="idx">{{ $i + 1 }}</span>
                                        <span class="ttl">{{ ucfirst($chapter->title) }}</span>
                                        <span class="meta">{{ $chapter->lessons->count() }} clases</span>
                                        <span class="chev"><i class="fa-solid fa-chevron-down"></i></span>
                                    </button>
                                    <div class="acc-body" {{ $isFirst ? '' : 'hidden' }}>
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
                    @if ($course->certifier)
                    <div class="certifier">
                        <h2>Certificador</h2>
                        <div class="cert-card cert-simple">
                            <div class="cert-avatar">
                                @if ($course->certifier->hasMedia('thumbnail'))
                                    <img src="{{ $course->certifier->getFirstMediaUrl('thumbnail') }}"
                                         alt="{{ $course->certifier->firstname }}" loading="lazy"
                                         onerror="this.style.display='none';this.nextElementSibling.style.display='';">
                                    <i class="fa-solid fa-user" style="display:none;"></i>
                                @else
                                    <i class="fa-solid fa-user"></i>
                                @endif
                            </div>
                            <div class="cert-info">
                                <div class="name">{{ $course->certifier->firstname }} {{ $course->certifier->lastname }}</div>
                                <span class="role">{{ $course->certifier->profession }}</span>
                                @if ($course->certifier->description != null)
                                    <div class="info">{!! clean($course->certifier->description, 'content') !!}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Opiniones de estudiantes --}}
                    @if (setting('reviews_enabled') && isset($reviews) && $reviews->isNotEmpty())
                        <div class="reviews-section">
                            <div class="rev-head">
                                <h2>Opiniones de estudiantes</h2>
                                <div class="rev-summary">
                                    <span class="rev-avg">{{ number_format($rating, 1) }}</span>
                                    <span class="rev-stars">
                                        @for ($s = 1; $s <= 5; $s++)
                                            <i class="{{ $s <= round($rating) ? 'fas' : 'far' }} fa-star"></i>
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

                </div>{{-- /.course-content --}}

                {{-- ===== Buy card ===== --}}
                <aside class="buy-card">
                    <div class="buy-media">
                        <img src="{{ $thumbUrl }}" alt="{{ $course->title }}" loading="lazy"
                             onerror="this.onerror=null;this.src='{{ asset('/pages/images/courses/default.jpg') }}';">
                        @if ($courseOnSale)
                            <span class="buy-off">-{{ $discountPct }}% OFF</span>
                        @endif
                        @if ($course->film != null)
                            <a href="{{ $course->film }}" class="play-badge popup-video"><i class="fa-solid fa-circle-play"></i> Vista previa</a>
                        @endif
                    </div>

                    <div class="buy-pad">

                        <div class="buy-price">
                            @if ($course->payment == 1)
                                @if ($courseOnSale)
                                    <span class="amt">$ {{ number_format($course->discount, 0, ',', '.') }}</span>
                                    <span class="cur">COP</span>
                                    <span class="note">-{{ $discountPct }}%</span>
                                @else
                                    <span class="amt">$ {{ number_format($course->price, 0, ',', '.') }}</span>
                                    <span class="cur">COP</span>
                                    <span class="note">Acceso de por vida</span>
                                @endif
                            @else
                                <span class="amt">GRATIS</span>
                                <span class="note">Acceso inmediato</span>
                            @endif
                        </div>

                        @if ($courseOnSale)
                            <div class="buy-was">Antes <s>$ {{ number_format($course->price, 0, ',', '.') }} COP</s> · De por vida</div>
                        @endif

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
                            </div>
                            <span class="buy-owned-note"><i class="fa-solid fa-circle-check"></i> Ya adquiriste este curso</span>
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
                                    <button type="submit" class="buy-primary" id="btnBuyNow">Compra ahora</button>
                                    <button type="submit" class="buy-outline" id="btnAddCart">Agregar al carrito</button>
                                </div>
                            </form>
                        @else
                            <div class="buy-actions">
                                <a href="{{ route('checkout', ['course', $course->slack]) }}" class="buy-primary">Obtener gratis</a>
                            </div>
                        @endif

                        @if (setting('whatsapp'))
                            <div class="buy-foot">
                                <span class="lbl">Para más detalles</span>
                                <a class="phone" href="tel:{{ setting('whatsapp') }}"><i class="fa-solid fa-phone"></i> {{ setting('whatsapp') }}</a>
                            </div>
                        @endif

                    </div>
                </aside>

            </div>{{-- /.course-grid --}}
        </div>
    </section>

    @include('pages.partials.sections.courses.related')

</div>{{-- /.dc-page --}}
@endsection

@push('scripts')
<script>
    $(document).ready(function () {

        // Acordeón (exclusivo por defecto, abre todos si expandAll activo)
        $(document).on('click', '.js-acc', function () {
            var $head = $(this);
            var $body = $head.next('.acc-body');

            if ($('body').hasClass('all-open')) {
                var willOpen = $body.is(':hidden');
                $head.toggleClass('open', willOpen);
                if (willOpen) $body.removeAttr('hidden').hide().slideDown(180);
                else $body.slideUp(180, function () { $(this).attr('hidden', ''); });
                return;
            }

            var isOpen = $head.hasClass('open');
            $('.js-acc').removeClass('open');
            $('.acc-body').slideUp(180, function () { $(this).attr('hidden', ''); });
            if (!isOpen) {
                $head.addClass('open');
                $body.removeAttr('hidden').hide().slideDown(180);
            }
        });

        // Expandir / contraer todo
        $('#expandAll').on('click', function () {
            var open = $('body').toggleClass('all-open').hasClass('all-open');
            $('.js-acc').toggleClass('open', open);
            if (open) {
                $('.acc-body').removeAttr('hidden').hide().slideDown(180);
                $(this).html('<i class="fa-solid fa-angles-up"></i> Contraer todo');
            } else {
                $('.acc-body').slideUp(180, function () { $(this).attr('hidden', ''); });
                $(this).html('<i class="fa-solid fa-angles-down"></i> Expandir todo');
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
