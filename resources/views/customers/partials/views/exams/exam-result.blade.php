{{-- El cuerpo del resultado va en su propio contenedor (.aula-result); el
     pie de navegación (.ar-foot) queda FUERA de él -- ver el mismo cambio en
     quiz-result.blade.php. --}}
<div class="aula-result">
    <span class="ar-badge">Resultado del examen</span>
    <h2 class="ar-title">{{ Str::upper($course->title) }}</h2>

@if ($score >= $passingScore)
    <div class="ar-icon ok">@include('customers.includes.icon', ['name' => 'award'])</div>
    <p class="ar-score">¡Felicitaciones, aprobaste el examen!</p>
    <p class="ar-sub">Respondiste correctamente {{ $correct > 0 ? $correct : 0 }} de {{ $count > 0 ? $count : 0 }} preguntas. Ya puedes descargar tu certificado.</p>
    <div class="ar-actions">
        <a class="ar-primary" href="{{ route('customers.certificate.download', $certificate->slack) }}" target="_blank">Descargar certificado</a>
        <a class="ar-secondary" href="{{ route('customers.dashboard') }}">Volver al inicio</a>
    </div>

    {{-- Calificación del curso (solo si el administrador habilitó las reseñas) --}}
    @if (setting('reviews_enabled'))
    <div class="ar-rate">
        @if (isset($userReview) && $userReview)
            <p class="ar-rate-done">
                <span class="ar-rate-stars">
                    @for ($s = 1; $s <= 5; $s++)
                        <span class="star-ic{{ $s <= $userReview->rating ? ' is-filled' : '' }}"><svg class="d-block" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m12 3 2.6 5.6L21 9.3l-4.5 4.3 1.1 6.4L12 17l-5.6 3 1.1-6.4L3 9.3l6.4-.7Z"/></svg></span>
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
                        <button type="button" class="star" data-val="{{ $s }}" aria-label="{{ $s }} estrellas">@include('customers.includes.icon', ['name' => 'star'])</button>
                    @endfor
                </div>
                <textarea name="comment" class="ar-comment" rows="3" placeholder="Cuéntanos tu opinión sobre el curso (opcional)…" maxlength="1000"></textarea>
                <button type="submit" class="ar-primary" id="rateSubmit" disabled>Enviar calificación</button>
            </form>
        @endif
    </div>
    @endif
@else
    <div class="ar-icon no">@include('customers.includes.icon', ['name' => 'refresh'])</div>
    <p class="ar-score">No superaste el examen</p>
    <p class="ar-sub">Acertaste {{ $correct > 0 ? $correct : 0 }} de {{ $count > 0 ? $count : 0 }} preguntas (necesitas {{ $topic->per_q_mark }}).
        @if ($topic->quiz_again)Revisa el detalle e inténtalo de nuevo.@else Revisa el contenido del curso.@endif
    </p>

    <div class="ar-review">
        @foreach ($answers as $answer)
            @php $isCorrect = $answer->approved == 1; @endphp
            <div class="q {{ $isCorrect ? 'is-ok' : 'is-no' }}">
                <span class="q-icon">
                    @if ($isCorrect)
                        @include('customers.includes.icon', ['name' => 'circle-check'])
                    @else
                        @include('customers.includes.icon', ['name' => 'circle-x'])
                    @endif
                </span>
                <span class="q-text">{{ $answer->question->question }}</span>
            </div>
        @endforeach
    </div>

    <div class="ar-actions">
        @if ($topic->quiz_again)
            <a class="ar-primary" href="{{ route('customers.exam.tryagain', $exam->id) }}">Reintentar examen</a>
        @endif
    </div>
@endif
</div>

@if ($lastCourseLesson)
    @php
        $lastLessonHref = $lastCourseLesson->type->slug == 'quiz'
            ? route('customers.courses.quiz', $lastCourseLesson->id)
            : route('customers.courses.lesion', $lastCourseLesson->id);
    @endphp
    <div class="lv-foot ar-foot">
        <a href="{{ $lastLessonHref }}" class="lv-fbtn" aria-label="Volver a la última lección">
            <span class="fb-txt"><span class="l">Anterior</span><span class="t">{{ ucfirst(Str::lower($lastCourseLesson->title)) }}</span></span>
        </a>
        <span></span>
    </div>
@endif
