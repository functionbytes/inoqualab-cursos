@extends('layouts.pages')

{{-- Detalle de curso — Modalidad 2 "Editorial con imagen y ruta"
     (canvas https://claude.ai/artifact/Lczkh16PL6sA9wxfD6QjaQ, tablero D).
     La Modalidad 1 es view.blade.php; el manager elige en Configuración › Sitio web
     (setting 'pages_course_detail_variant'). Mismos datos del controlador en ambas. --}}

@section('active', 'courses')

@section('title', str($course->title)->lower()->ucfirst())

@push('css')
<link rel="stylesheet" href="{{ url('/pages/css/storefront.css') }}?v={{ @filemtime(public_path('pages/css/storefront.css')) ?: '1' }}">
<link rel="stylesheet" href="{{ url('/pages/css/partials/components/course-card.css') }}?v={{ @filemtime(public_path('pages/css/partials/components/course-card.css')) ?: '1' }}">
<link rel="stylesheet" href="{{ url('/pages/css/views/courses/view-editorial.css') }}?v={{ @filemtime(public_path('pages/css/views/courses/view-editorial.css')) ?: '1' }}">
@endpush

@section('content')

@php
    $thumbUrl = $course->cardImageUrl(asset('/pages/images/courses/default.jpg'));
    $courseTitle = str($course->title)->lower()->ucfirst();
    $lessonsCount = count($leasons);
    $chaptersCount = count($chapters);
    $isPaid = $course->payment == 1;
    $courseOnSale = $isPaid && $course->promotion == 1 && $course->discount < $course->price;
    $discountPct = $courseOnSale ? round(($course->price - $course->discount) / $course->price * 100) : 0;
    $effPrice = $courseOnSale ? $course->discount : $course->price;
    $rating = (float) ($course->rating ?? 0);

    // Resumen del hero: primer párrafo de la descripción, en texto plano.
    $lead = null;
    if ($course->short) {
        preg_match('/<p[^>]*>(.*?)<\/p>/is', $course->short, $firstParagraph);
        $plain = trim(html_entity_decode(strip_tags($firstParagraph[1] ?? $course->short)));
        // Frases completas hasta ~200 caracteres; si la primera ya es más larga, se corta por palabras.
        $lead = '';
        foreach (preg_split('/(?<=[.!?])\s+/u', $plain) as $sentence) {
            if ($lead !== '' && mb_strlen($lead.' '.$sentence) > 200) {
                break;
            }
            $lead = trim($lead.' '.$sentence);
        }
        $lead = \Illuminate\Support\Str::words($lead, 34);
    }

    // Objetivos: el campo "learn" es una lista <ul><li>; cada <li> es una tarjeta.
    // Si no trae <li> (texto libre), se muestra el HTML tal cual.
    $objectives = [];
    if ($course->learn) {
        preg_match_all('/<li[^>]*>(.*?)<\/li>/is', clean($course->learn, 'content'), $liMatches);
        $objectives = array_values(array_filter(array_map(
            fn ($li) => trim(html_entity_decode(strip_tags($li))),
            $liMatches[1] ?? []
        )));
    }
    $objectivesVisible = 6;

    // Tipo de cada clase según course_lessons.type_id (tabla course_types),
    // agrupado en las 3 categorías del diseño: Video, Quiz y Material.
    $lessonCategory = fn ($typeId) => match ((int) $typeId) {
        1, 2 => 'video',
        6 => 'quiz',
        default => 'doc',
    };
    $categoryLabels = ['video' => 'Video', 'quiz' => 'Quiz', 'doc' => 'Material'];
    $lessonsVisible = 8;

    $modules = $course->chapters->map(function ($chapter) use ($lessonCategory) {
        $lessons = $chapter->lessons->where('available', 1)->values();
        $counts = $lessons->countBy(fn ($lesson) => $lessonCategory($lesson->type_id));

        return [
            'chapter' => $chapter,
            'lessons' => $lessons,
            'videos' => $counts->get('video', 0),
            'quizzes' => $counts->get('quiz', 0),
            'docs' => $counts->get('doc', 0),
        ];
    });
    $maxLessons = max(1, $modules->max(fn ($m) => $m['lessons']->count()) ?? 1);

    $section = 0;
    $certifier = $course->certifier;
@endphp

<div class="cde-page">

    {{-- ===== Hero con imagen + tarjeta de compra ===== --}}
    <section class="cde-hero">
        <img class="cde-hero-bg" src="{{ $thumbUrl }}" alt="" aria-hidden="true">
        <div class="cde-hero-shade"></div>
        <div class="cde-container cde-hero-inner">
            <div class="cde-hero-copy">
                <nav class="cde-crumb" aria-label="Ruta de navegación">
                    <a href="{{ route('index') }}">Inicio</a> /
                    <a href="{{ route('courses') }}">Cursos</a> /
                    <span>{{ \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::lower($course->categorie?->title ?? 'Curso')) }}</span>
                </nav>

                @if ($course->categorie)
                    <span class="cde-chip">{{ $course->categorie->title }}</span>
                @endif

                <h1 class="cde-title">{{ $courseTitle }}</h1>

                @if ($lead)
                    <p class="cde-lead">{{ $lead }}</p>
                @endif

                @if ($course->film != null)
                    <a href="{{ $course->film }}" class="cde-btn cde-btn--white cde-preview js-course-preview">Ver vista previa</a>
                @endif
            </div>

            <aside class="cde-buy" id="cdeHeroActions">
                <div class="cde-buy-kicker">{{ $alreadyOwned ? 'Tu curso' : 'Inscríbete hoy' }}</div>

                <div class="cde-price">
                    @if (! $isPaid)
                        <div class="cde-price-row">
                            <span class="cde-price-now">Gratis</span>
                            <span class="cde-price-note">Acceso inmediato</span>
                        </div>
                    @else
                        @if ($courseOnSale)
                            <div class="cde-price-was">Antes $ {{ number_format($course->price, 0, ',', '.') }}</div>
                        @endif
                        <div class="cde-price-row">
                            <span class="cde-price-now">$ {{ number_format($effPrice, 0, ',', '.') }}</span>
                            <span class="cde-price-cur">COP</span>
                            @if ($courseOnSale)
                                <span class="cde-price-pill">-{{ $discountPct }}%</span>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="cde-actions">
                    @if ($alreadyOwned)
                        <a href="{{ $inscriptionSlack ? route('customers.courses.content', $inscriptionSlack) : route('customers.courses') }}" class="cde-btn cde-btn--dark">
                            {{ $inscriptionSlack ? 'Continuar aprendiendo' : 'Ir a mis cursos' }}
                        </a>
                        <span class="cde-owned">Ya adquiriste este curso</span>
                    @elseif ($isPaid)
                        {{-- Mismo formulario que la Modalidad 1: lo envía por AJAX public/pages/js/layout-cart.js --}}
                        <form class="form-add-to-cart cde-actions-form" id="courseBuyForm" action="{{ route('cart.add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="type" value="course">
                            <input type="hidden" name="slack" value="{{ $course->slack }}">
                            <input type="hidden" name="qty" value="1">
                            <input type="hidden" name="buy_now" id="buyNow" value="">
                            <button type="submit" class="cde-btn cde-btn--dark js-buy-now">
                                Comprar ahora
                            </button>
                            <button type="submit" class="cde-btn cde-btn--outline js-add-cart">
                                Agregar al carrito
                            </button>
                        </form>
                    @else
                        <a href="{{ route('checkout', ['course', $course->slack]) }}" class="cde-btn cde-btn--dark">
                            Obtener gratis
                        </a>
                    @endif
                </div>

                <ul class="cde-specs">
                    @if ($course->duration)
                        <li><span><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>Duración</span><b>{{ $course->duration }} {{ $course->duration == 1 ? 'hora' : 'horas' }}</b></li>
                    @endif
                    <li><span><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M10 8.5l5 3.5-5 3.5z" class="fill"/></svg>Clases</span><b>{{ $lessonsCount }}</b></li>
                    <li><span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3l9 5-9 5-9-5z"/><path d="M3 13l9 5 9-5"/></svg>Módulos</span><b>{{ $chaptersCount }}</b></li>
                    <li><span><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9.5 9.5a2.5 2.5 0 1 1 3.3 2.4c-.5.2-.8.6-.8 1.1v.5M12 16.5v.3"/></svg>Examen</span><b>{{ $course->exam == 1 ? 'Sí' : 'No' }}</b></li>
                    <li><span><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="9" r="6"/><path d="M9 14l-1.5 7L12 19l4.5 2L15 14"/></svg>Certificado</span><b>{{ $course->certificate == 1 ? 'Sí' : 'No' }}</b></li>
                    @if ($isPaid)
                        <li><span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 9a3 3 0 1 0 0 6c3 0 7-6 10-6a3 3 0 1 1 0 6c-3 0-7-6-10-6z"/></svg>Acceso</span><b>De por vida</b></li>
                    @endif
                </ul>
            </aside>
        </div>
    </section>

    {{-- ===== Contenido editorial ===== --}}
    <main class="cde-main">

        @if ($course->short != null)
            <section class="cde-section">
                <div class="cde-kicker">{{ str_pad(++$section, 2, '0', STR_PAD_LEFT) }} · Descripción</div>
                <h2 class="cde-h2">De qué trata este curso</h2>
                <div class="cde-prose">{!! clean($course->short, 'content') !!}</div>
            </section>
        @endif

        @if ($course->learn != null)
            <section class="cde-section">
                <div class="cde-kicker">{{ str_pad(++$section, 2, '0', STR_PAD_LEFT) }} · Objetivos</div>
                <h2 class="cde-h2">Qué aprenderás</h2>
                @if (count($objectives))
                    <div class="cde-objectives" id="cdeObjectives">
                        @foreach ($objectives as $i => $objective)
                            <div class="cde-objective {{ $i >= $objectivesVisible ? 'is-extra' : '' }}" @if ($i >= $objectivesVisible) hidden @endif>
                                <div class="cde-objective-num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</div>
                                <p>{{ $objective }}</p>
                            </div>
                        @endforeach
                    </div>
                    @if (count($objectives) > $objectivesVisible)
                        <button type="button" class="cde-link js-objectives-toggle"
                                data-label-more="Ver los {{ count($objectives) }} objetivos"
                                data-label-less="Ver menos objetivos">Ver los {{ count($objectives) }} objetivos</button>
                    @endif
                @else
                    <div class="cde-prose">{!! clean($course->learn, 'content') !!}</div>
                @endif
            </section>
        @endif

        @if ($modules->isNotEmpty())
            <section class="cde-section">
                <div class="cde-kicker">{{ str_pad(++$section, 2, '0', STR_PAD_LEFT) }} · Ruta</div>
                <h2 class="cde-h2">Ruta del curso</h2>
                <p class="cde-sub">{{ $chaptersCount }} {{ $chaptersCount == 1 ? 'módulo' : 'módulos' }} · {{ $lessonsCount }} clases · avanza a tu ritmo y obtén tu certificado al finalizar.</p>
                <div class="cde-route">
                    @foreach ($modules as $i => $module)
                        <div class="cde-step {{ $loop->first ? 'is-active' : '' }}">
                            <div class="cde-step-label">Paso {{ $i + 1 }}</div>
                            <div class="cde-step-title">{{ $module['chapter']->title }}</div>
                            <div class="cde-step-bar"><span data-width="{{ round($module['lessons']->count() / $maxLessons * 100) }}"></span></div>
                            <div class="cde-step-count">{{ $module['lessons']->count() }} {{ $module['lessons']->count() == 1 ? 'clase' : 'clases' }}</div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="cde-section">
                <div class="cde-kicker">{{ str_pad(++$section, 2, '0', STR_PAD_LEFT) }} · Contenido</div>
                <h2 class="cde-h2">Contenido del curso</h2>
                <div class="cde-acc-actions">
                    <span class="cde-acc-summary">{{ $chaptersCount }} {{ $chaptersCount == 1 ? 'módulo' : 'módulos' }} · {{ $lessonsCount }} clases @if ($course->duration) · {{ $course->duration }} {{ $course->duration == 1 ? 'hora' : 'horas' }} @endif</span>
                    <button type="button" class="cde-link" id="expandAll">Expandir todo</button>
                </div>

                {{-- Las clases js-acc / acc-body / #expandAll las maneja public/pages/js/views/courses/view.js --}}
                <div class="cde-acc">
                    @foreach ($modules as $i => $module)
                        @php
                            $moduleLessons = $module['lessons'];
                            $breakdown = collect([
                                $module['videos'] ? $module['videos'].' '.($module['videos'] == 1 ? 'video' : 'videos') : null,
                                $module['quizzes'] ? $module['quizzes'].' '.($module['quizzes'] == 1 ? 'quiz' : 'quices') : null,
                                $module['docs'] ? $module['docs'].' '.($module['docs'] == 1 ? 'material' : 'materiales') : null,
                            ])->filter()->implode(' · ');
                        @endphp
                        <div class="cde-acc-item">
                            <button type="button" class="cde-acc-head js-acc {{ $loop->first ? 'open' : '' }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                                <span class="cde-acc-idx">{{ $i + 1 }}</span>
                                <span class="cde-acc-text">
                                    <span class="cde-acc-title">{{ \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::lower($module['chapter']->title)) }}</span>
                                    @if ($moduleLessons->count())
                                        <span class="cde-acc-mix">
                                            <span class="cde-mix-bar" aria-hidden="true">
                                                @foreach (['videos' => 'is-video', 'quizzes' => 'is-quiz', 'docs' => 'is-doc'] as $key => $cls)
                                                    @if ($module[$key])
                                                        <span class="{{ $cls }}" data-width="{{ round($module[$key] / $moduleLessons->count() * 100, 1) }}"></span>
                                                    @endif
                                                @endforeach
                                            </span>
                                            {{ $breakdown }}
                                        </span>
                                    @endif
                                </span>
                                <span class="cde-acc-count">{{ $moduleLessons->count() }} {{ $moduleLessons->count() == 1 ? 'clase' : 'clases' }}</span>
                                <span class="cde-acc-chev" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg></span>
                            </button>
                            <div class="acc-body cde-acc-body" {{ $loop->first ? '' : 'hidden' }}>
                                <div class="cde-lessons">
                                @foreach ($moduleLessons as $k => $lesson)
                                    @php $category = $lessonCategory($lesson->type_id); @endphp
                                    <div class="cde-lesson {{ $k >= $lessonsVisible ? 'is-extra' : '' }}" @if ($k >= $lessonsVisible) hidden @endif>
                                        <span class="cde-type is-{{ $category }}" aria-hidden="true">
                                            @if ($category === 'video')
                                                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M10 8.5l5 3.5-5 3.5z" class="fill"/></svg>
                                            @elseif ($category === 'quiz')
                                                <svg viewBox="0 0 24 24"><path d="M9 11l2.5 2.5L16 9"/><rect x="4" y="4" width="16" height="16" rx="3"/></svg>
                                            @else
                                                <svg viewBox="0 0 24 24"><path d="M7 3h7l4 4v14H7z"/><path d="M14 3v4h4M10 12h5M10 16h5"/></svg>
                                            @endif
                                        </span>
                                        <span class="cde-lesson-title">{{ ucfirst(\Illuminate\Support\Str::lower($lesson->title)) }}</span>
                                        <span class="cde-lesson-tag">{{ $categoryLabels[$category] }}</span>
                                    </div>
                                @endforeach
                                </div>
                                @if ($moduleLessons->count() > $lessonsVisible)
                                    <button type="button" class="cde-more js-lessons-toggle"
                                            data-label-more="Ver las {{ $moduleLessons->count() }} clases del módulo"
                                            data-label-less="Ver menos clases">
                                        <span>Ver las {{ $moduleLessons->count() }} clases del módulo</span>
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($certifier)
            <section class="cde-section">
                <div class="cde-kicker">{{ str_pad(++$section, 2, '0', STR_PAD_LEFT) }} · Certificador</div>
                <h2 class="cde-h2">Quién certifica</h2>
                <div class="cde-certifier">
                    <div class="cde-certifier-avatar">
                        @if ($certifier->hasMedia('thumbnail'))
                            <img src="{{ $certifier->getFirstMediaUrl('thumbnail') }}" alt="{{ $certifier->firstname }}" loading="lazy"
                                 class="js-img-fallback" data-fallback-action="hide-sibling">
                            <span class="js-hidden">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($certifier->firstname, 0, 1).\Illuminate\Support\Str::substr($certifier->lastname, 0, 1)) }}</span>
                        @else
                            <span>{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($certifier->firstname, 0, 1).\Illuminate\Support\Str::substr($certifier->lastname, 0, 1)) }}</span>
                        @endif
                    </div>
                    <div>
                        <div class="cde-certifier-name">{{ $certifier->firstname }} {{ $certifier->lastname }}</div>
                        @if ($certifier->profession)
                            <span class="cde-certifier-role">{{ $certifier->profession }}</span>
                        @endif
                        @if ($certifier->description != null)
                            <div class="cde-certifier-info">{!! clean($certifier->description, 'content') !!}</div>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        @if (setting('reviews_enabled') && isset($reviews) && $reviews->isNotEmpty())
            <section class="cde-section">
                <div class="cde-kicker">{{ str_pad(++$section, 2, '0', STR_PAD_LEFT) }} · Opiniones</div>
                <h2 class="cde-h2">Opiniones de estudiantes</h2>
                <p class="cde-sub">{{ number_format($rating, 1) }} de 5 · {{ $reviewsCount }} {{ $reviewsCount == 1 ? 'reseña' : 'reseñas' }}</p>
                <div class="cde-reviews">
                    @foreach ($reviews as $review)
                        @php
                            $fn = $review->user->firstname ?? 'Estudiante';
                            $ln = $review->user->lastname ?? '';
                        @endphp
                        <div class="cde-review">
                            <div class="cde-review-top">
                                <span class="cde-review-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($fn, 0, 1)) }}</span>
                                <span class="cde-review-name">{{ ucfirst(\Illuminate\Support\Str::lower($fn)) }} {{ $ln ? ucfirst(\Illuminate\Support\Str::lower(\Illuminate\Support\Str::substr($ln, 0, 1))).'.' : '' }}</span>
                                <span class="cde-stars sm" aria-label="{{ $review->rating }} de 5">
                                    @for ($s = 1; $s <= 5; $s++)
                                        <svg viewBox="0 0 24 24" class="{{ $s <= $review->rating ? 'on' : '' }}"><path d="M12 2.8l2.8 5.8 6.3.9-4.6 4.4 1.1 6.3L12 17.2l-5.6 3 1.1-6.3L2.9 9.5l6.3-.9z"/></svg>
                                    @endfor
                                </span>
                            </div>
                            <p>{{ $review->comment }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

    </main>

    @include('pages.partials.sections.courses.related')

    {{-- ===== Barra de compra fija (aparece al pasar el hero) ===== --}}
    <div class="cde-bar" id="cdeBar" aria-hidden="true">
        <div class="cde-container cde-bar-inner">
            <div class="cde-bar-info">
                <div class="cde-bar-title">{{ $courseTitle }}</div>
                <div class="cde-bar-meta">{{ $lessonsCount }} clases @if ($course->certificate == 1) · Certificado al finalizar @endif</div>
            </div>
            <div class="cde-bar-price">
                @if (! $isPaid)
                    <div class="cde-bar-now">Gratis</div>
                @else
                    @if ($courseOnSale)
                        <div class="cde-bar-was">$ {{ number_format($course->price, 0, ',', '.') }}</div>
                    @endif
                    <div class="cde-bar-now">$ {{ number_format($effPrice, 0, ',', '.') }} <small>COP</small></div>
                @endif
            </div>
            @if ($alreadyOwned)
                <a href="{{ $inscriptionSlack ? route('customers.courses.content', $inscriptionSlack) : route('customers.courses') }}" class="cde-btn cde-btn--dark" tabindex="-1">Continuar</a>
            @elseif ($isPaid)
                <button type="submit" form="courseBuyForm" class="cde-btn cde-btn--dark js-buy-now" tabindex="-1">
                    Comprar ahora
                </button>
            @else
                <a href="{{ route('checkout', ['course', $course->slack]) }}" class="cde-btn cde-btn--dark" tabindex="-1">Obtener gratis</a>
            @endif
        </div>
    </div>

</div>
@endsection

@push('scripts')
    <script src="{{ asset('pages/js/views/courses/view.js') }}?v={{ @filemtime(public_path('pages/js/views/courses/view.js')) ?: '1' }}"></script>
    <script src="{{ asset('pages/js/views/courses/view-editorial.js') }}?v={{ @filemtime(public_path('pages/js/views/courses/view-editorial.js')) ?: '1' }}"></script>
@endpush
