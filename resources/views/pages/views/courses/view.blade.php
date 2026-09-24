@extends('layouts.pages')

@section('active', 'courses')

@section('title', str($course->title)->lower()->ucfirst())

@push('css')
<link rel="stylesheet" href="{{ url('/pages/css/storefront.css') }}?v={{ @filemtime(public_path('pages/css/storefront.css')) ?: '1' }}">
<link rel="stylesheet" href="{{ url('/pages/css/partials/components/course-card.css') }}?v={{ @filemtime(public_path('pages/css/partials/components/course-card.css')) ?: '1' }}">
@endpush

@section('content')

@php
    $thumbUrl = $course->cardImageUrl(asset('/pages/images/courses/default.jpg'));
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
            <h1>{{ str($course->title)->lower()->ucfirst() }}</h1>
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
                                         class="js-img-fallback" data-fallback-action="hide-sibling">
                                    <i class="fa-solid fa-user js-hidden"></i>
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
                             class="js-img-fallback" data-fallback-src="{{ asset('/pages/images/courses/default.jpg') }}">
                        @if ($courseOnSale)
                            <span class="buy-off">-{{ $discountPct }}% OFF</span>
                        @endif
                        @if ($course->film != null)
                            <a href="{{ $course->film }}" class="play-badge js-course-preview"><i class="fa-solid fa-circle-play"></i> Vista previa</a>
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
    <script src="{{ asset('pages/js/views/courses/view.js') }}"></script>
@endpush
