{{-- El cuerpo del resultado va en su propio contenedor (.aula-result); el
     pie Anterior/Siguiente (.ar-foot) queda FUERA de él -- antes ambos
     compartían un mismo card, así que el pie de navegación heredaba el
     padding/ancho pensado para un card angosto en vez del ancho completo del
     panel, y el padding inferior de la vista dependía de un solo valor
     compartido entre dos elementos con propósitos distintos. --}}
<div class="aula-result">
    <span class="ar-badge">Resultado del quiz</span>
    <h2 class="ar-title">{{ Str::upper($lesson->title) }}</h2>

    @if ($score >= $passingScore)
        <div class="ar-icon ok">@include('customers.includes.icon', ['name' => 'check'])</div>
        <p class="ar-score">¡Aprobaste el quiz!</p>
        <p class="ar-sub">Respondiste correctamente {{ $correct > 0 ? $correct : 0 }} de {{ $count > 0 ? $count : 0 }} preguntas. Continúa con la siguiente lección.</p>
    @else
        <div class="ar-icon no">@include('customers.includes.icon', ['name' => 'x'])</div>
        <p class="ar-score">No superaste el quiz</p>
        <p class="ar-sub">Acertaste {{ $correct > 0 ? $correct : 0 }} de {{ $count > 0 ? $count : 0 }} preguntas.
            @if ($topic->quiz_again >= 1)Inténtalo nuevamente.@else Revisa el contenido de la lección.@endif Necesitas {{ $topic->per_q_mark }} de {{ $count }} correctas.
        </p>
        @if ($topic->quiz_again >= 1)
            <div class="ar-actions">
                <a class="ar-primary" href="{{ route('customers.quiz.tryagain', $quiz->id) }}">@include('customers.includes.icon', ['name' => 'refresh']) Reintentar</a>
            </div>
        @endif
    @endif
</div>

<div class="lv-foot ar-foot">
    @if ($prevLesson && $prevLesson !== 'true')
        <form action="{{ route('customers.courses.prev') }}" method="POST" onsubmit="var b=this.querySelector('button');b.disabled=true;">
            @csrf
            <input type="hidden" name="course" value="{{ $course->id }}">
            <input type="hidden" name="lesson" value="{{ $lesson->id }}">
            <input type="hidden" name="user" value="{{ $user->id }}">
            <button type="submit" class="lv-fbtn" aria-label="Lección anterior">
                @include('customers.includes.icon', ['name' => 'arrow-left'])
                <span class="fb-txt">
                    <span class="l">Anterior</span>
                    <span class="t">{{ ucfirst(Str::lower($prevLesson->title)) }}</span>
                </span>
            </button>
        </form>
    @else
        <span></span>
    @endif

    @if ($score >= $passingScore)
        <form action="{{ route('customers.quiz.realized') }}" method="POST" onsubmit="var b=this.querySelector('button');b.disabled=true;">
            @csrf
            <input type="hidden" name="course" value="{{ $course->id }}">
            <input type="hidden" name="lesson" value="{{ $lesson->id }}">
            <input type="hidden" name="user" value="{{ $user->id }}">
            <button type="submit" class="lv-fbtn next" aria-label="Continuar">
                <span class="fb-txt"><span class="l">Siguiente</span><span class="t">Continuar</span></span>
                @include('customers.includes.icon', ['name' => 'arrow-right'])
            </button>
        </form>
    @else
        <span></span>
    @endif
</div>
