{{-- Tarjeta de curso del frontend público: catálogo (/courses), relacionados
     (detalle de curso) y secciones del home. El diseño (a/b/c/d) lo elige el
     manager en Configuración › Sitio web (setting 'pages_course_card_variant').
     Estilos: public/pages/css/partials/components/course-card.css (cargarlo con
     @push('css') en la vista que incluya este parcial).

     Props:
     - $course (requerido) — con categorie y media cargados y withCount(['lessons', 'chapters'])
     - $filterable (opcional, bool) — agrega los data-* que usa el filtrado de
       public/pages/js/views/courses/index.js
     - $variant (opcional) — fuerza un diseño (lo usa la vista previa del panel) --}}
@php
    $cardVariant = $variant ?? setting('pages_course_card_variant', 'c');
    if (! in_array($cardVariant, ['a', 'b', 'c', 'd'], true)) {
        $cardVariant = 'c';
    }

    $isFree = $course->payment != 1;
    $onSale = ! $isFree && $course->promotion == 1 && $course->discount < $course->price;
    $effPrice = $isFree ? 0 : ($onSale ? $course->discount : $course->price);
    $offPct = $onSale && $course->price > 0 ? (int) round(($course->price - $course->discount) / $course->price * 100) : 0;
    $rating = (float) ($course->rating ?? 0);
    $lessons = $course->lessons_count ?? 0;
    $chapters = $course->chapters_count ?? 0;

    $card = [
        'url' => route('courses.view', [$course->slack]),
        'title' => (string) str($course->title)->lower()->ucfirst(),
        'category' => $course->categorie->title ?? null,
        'isFree' => $isFree,
        'onSale' => $onSale,
        'price' => number_format($effPrice, 0, ',', '.'),
        'oldPrice' => number_format((float) $course->price, 0, ',', '.'),
        'offPct' => $offPct,
        'rating' => $rating,
        'ratingText' => $rating > 0 ? number_format($rating, 1) : null,
        'lessons' => $lessons,
        'lessonsLabel' => $lessons == 1 ? 'clase' : 'clases',
        'chapters' => $chapters,
        'chaptersLabel' => $chapters == 1 ? 'tema' : 'temas',
    ];
@endphp
@include('pages.partials.components.course-card.'.$cardVariant, ['card' => $card, 'course' => $course, 'filterable' => $filterable ?? false])
