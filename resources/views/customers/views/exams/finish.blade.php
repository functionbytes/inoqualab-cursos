@extends('layouts.customers')

@section('title', $course->title )

@section('head')
    @php $url = URL::current(); @endphp
    <meta name="title" content="{{ $course->title }}">
    <meta name="description" content="{{ $course->short_detail }} ">
    <meta property="og:title" content="{{ $course->title }} ">
    <meta property="og:url" content="{{ $url }}">
    <meta property="og:type" content="website">
    <link rel="canonical" href="{{ url()->full() }}" />
    <meta name="robots" content="all">
@endsection

@push('css')
    <link rel="stylesheet" href="{{ url('/customers/css/aula.css') }}">
@endpush

@section('content')

<div class="aula">
    <div class="aula-result">
        <span class="ar-badge">Resultado del examen</span>
        <h2 class="ar-title">{{ Str::upper($course->title) }}</h2>

        @if ($score >= $passingScore)
            <div class="ar-icon ok"><i class="fa-solid fa-award"></i></div>
            <p class="ar-score">¡Felicitaciones, aprobaste el examen!</p>
            <p class="ar-sub">Respondiste correctamente {{ $correct > 0 ? $correct : 0 }} de {{ $count > 0 ? $count : 0 }} preguntas. Ya puedes descargar tu certificado.</p>
            <div class="ar-actions">
                <a class="ar-primary" href="{{ route('customers.certificate.download', $certificate->slack) }}" target="_blank"><i class="fa-solid fa-download"></i> Descargar certificado</a>
                <a class="ar-secondary" href="{{ route('customers.dashboard') }}">Volver al inicio</a>
            </div>

            {{-- Calificación del curso (solo si el administrador habilitó las reseñas) --}}
            @if (setting('reviews_enabled'))
            <div class="ar-rate">
                @if (isset($userReview) && $userReview)
                    <p class="ar-rate-done">
                        <span class="ar-rate-stars">
                            @for ($s = 1; $s <= 5; $s++)
                                <i class="{{ $s <= $userReview->rating ? 'fa-solid' : 'fa-regular' }} fa-star"></i>
                            @endfor
                        </span>
                        Ya calificaste este curso con {{ $userReview->rating }}/5. ¡Gracias!
                    </p>
                @else
                    <h3 class="ar-rate-title">¿Cómo calificarías este curso?</h3>
                    <form action="{{ route('customers.courses.review') }}" method="POST" class="ar-rate-form">
                        @csrf
                        <input type="hidden" name="course" value="{{ $course->slack }}">
                        <input type="hidden" name="rating" id="rateValue" value="">
                        <div class="ar-stars" id="rateStars">
                            @for ($s = 1; $s <= 5; $s++)
                                <button type="button" class="star" data-val="{{ $s }}" aria-label="{{ $s }} estrellas"><i class="fa-regular fa-star"></i></button>
                            @endfor
                        </div>
                        <textarea name="comment" class="ar-comment" rows="3" placeholder="Cuéntanos tu opinión sobre el curso (opcional)…" maxlength="1000"></textarea>
                        <button type="submit" class="ar-primary" id="rateSubmit" disabled><i class="fa-solid fa-paper-plane"></i> Enviar calificación</button>
                    </form>
                @endif
            </div>
            @endif
        @else
            <div class="ar-icon no"><i class="fa-solid fa-xmark"></i></div>
            <p class="ar-score">No superaste el examen</p>
            <p class="ar-sub">Acertaste {{ $correct > 0 ? $correct : 0 }} de {{ $count > 0 ? $count : 0 }} preguntas (necesitas {{ $topic->per_q_mark }}).
                @if ($topic->quiz_again)Revisa el detalle e inténtalo de nuevo.@else Revisa el contenido del curso.@endif
            </p>

            <div class="ar-review">
                @foreach ($answers as $answer)
                    <div class="q">
                        <span>{{ $answer->question->question }}</span>
                        @if ($answer->approved == 1)
                            <i class="fa-solid fa-circle-check ok"></i>
                        @else
                            <i class="fa-solid fa-circle-xmark no"></i>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="ar-actions">
                @if ($topic->quiz_again)
                    <a class="ar-primary" href="{{ route('customers.exam.tryagain', $exam->id) }}"><i class="fa-solid fa-rotate-right"></i> Reintentar examen</a>
                @endif
            </div>
        @endif
    </div>
</div>

@push('css')
<style>
    .ar-rate { margin-top: 28px; padding-top: 24px; border-top: 1px solid #e7ecf1; }
    .ar-rate-title { font-size: 16px; font-weight: 700; margin: 0 0 14px; color: #1b2a3a; }
    .ar-stars { display: inline-flex; gap: 6px; margin-bottom: 14px; }
    .ar-stars .star { background: none; border: none; cursor: pointer; font-size: 30px; line-height: 1; color: #f5b740; padding: 2px; }
    .ar-stars .star i { transition: transform .1s ease; }
    .ar-stars .star:hover i { transform: scale(1.15); }
    .ar-comment { width: 100%; max-width: 460px; border: 1.5px solid #e7ecf1; border-radius: 10px; padding: 12px 14px; font-size: 14px; font-family: inherit; resize: vertical; display: block; margin: 0 auto 14px; }
    .ar-comment:focus { outline: none; border-color: #008bcd; box-shadow: 0 0 0 3px rgba(0,139,205,.12); }
    .ar-rate-done { font-size: 15px; font-weight: 700; color: #006fa3; display: inline-flex; align-items: center; gap: 10px; flex-wrap: wrap; justify-content: center; }
    .ar-rate-stars { color: #f5b740; font-size: 18px; }
    .ar-primary:disabled { opacity: .5; cursor: not-allowed; }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        var wrap = document.getElementById('rateStars');
        if (!wrap) return;
        var stars  = wrap.querySelectorAll('.star');
        var input  = document.getElementById('rateValue');
        var submit = document.getElementById('rateSubmit');

        function paint(val) {
            stars.forEach(function (s) {
                var v = parseInt(s.getAttribute('data-val'), 10);
                s.querySelector('i').className = (v <= val ? 'fa-solid' : 'fa-regular') + ' fa-star';
            });
        }
        stars.forEach(function (s) {
            var v = parseInt(s.getAttribute('data-val'), 10);
            s.addEventListener('mouseenter', function () { paint(v); });
            s.addEventListener('click', function () {
                input.value = v;
                paint(v);
                if (submit) submit.disabled = false;
            });
        });
        wrap.addEventListener('mouseleave', function () { paint(parseInt(input.value, 10) || 0); });
    })();
</script>
@endpush
@endsection
