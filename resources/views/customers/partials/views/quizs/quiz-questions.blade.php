@if(setting('aula_version') == '2')
    {{-- Barra sticky solo-mobile con el toggle del rail (ver misma nota en
         lesson-content.blade.php). El guard de salida del quiz
         (assessment-exit-guard.blade.php) ya excluye los clics dentro de
         .lv-rail-toggle de su intercepción de navegación -- sigue aplicando
         igual aunque el botón se movió de contenedor. --}}
    <div class="lv-mobile-bar">
        <button type="button" class="lv-rail-toggle" aria-label="Ver clases del curso" aria-expanded="false" aria-controls="lvRail">
            @include('customers.includes.icon', ['name' => 'menu'])
        </button>
    </div>
@endif

<div class="lv-lhead">
    <div>
        <span class="lk">@include('customers.includes.icon', ['name' => 'circle-question']) Evaluación</span>
        <h1>{{ ucfirst($lesson->title) }}</h1>
        <div class="lmeta">
            <span>@include('customers.includes.icon', ['name' => 'list-check']) {{ $questions->count() }} preg.</span>
            <span>@include('customers.includes.icon', ['name' => 'layers']) {{ $lesson->chapter->title ?? $course->title }}</span>
        </div>
    </div>
</div>

<div class="quiz-rule"></div>

<div class="progress-track" aria-label="Progreso">
    <div id="progressbar" class="progress-fill" style="--pct: 0%"></div>
</div>

<div id="question_block" class="question-block">

    <input type="hidden" id="type" name="type" value="{{ $topic->type }}">
    <input type="hidden" id="id" name="id" value="{{ $topic->id }}">

    @php
        $users = $answers;
        $que_count = $questions->count();
        $count = 1;
    @endphp

    @if ($topic->type == 0)
        <div id="question-div">
            <form action="{{ route('customers.quiz.store', $topic->id) }}" method="POST" id="question-form">
                {{ csrf_field() }}

                @php $count = 1; @endphp

                <input type="hidden" id="quiz" name="quiz" value="{{ $quiz->id }}">
                <input type="hidden" name="question_id[{{ $count }}]" value="{{ $questions[0]['id'] }}">

                <div id="more_quiz0">
                    <div class="quiz-step" id="quiz1">
                        <div class="quiz-qhead">
                            <div class="quiz-q">{{ $questions[0]['question'] }}</div>
                            <div class="quiz-count"><span id="quizNumber">{{ $count }}</span>/<span class="total-step">{{ $que_count }}</span></div>
                        </div>
                        <div class="quiz-options">
                            <label class="quiz-opt">
                                <input type="radio" name="answer[{{ $count }}]" value="true" class="required quiz-native-input">
                                <span class="radio"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
                                Verdadero
                            </label>
                            <label class="quiz-opt">
                                <input type="radio" name="answer[{{ $count }}]" value="false" class="required quiz-native-input">
                                <span class="radio"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
                                Falso
                            </label>
                        </div>
                    </div>
                </div>

                @foreach ($questions as $key => $question)
                    @if ($key > 0)
                        <div class="d-none-js" id="more_quiz{{ $key }}">
                            <div class="quiz-step" id="quiz{{ $key + 1 }}">
                                <input type="hidden" name="question_id[{{ $count }}]" value="{{ $question['id'] }}">
                                <div class="quiz-qhead">
                                    <div class="quiz-q">{{ $question['question'] }}</div>
                                    <div class="quiz-count"><span id="quizNumber">{{ $count }}</span>/<span class="total-step">{{ $que_count }}</span></div>
                                </div>
                                <div class="quiz-options">
                                    <label class="quiz-opt">
                                        <input type="radio" name="answer[{{ $count }}]" value="true" class="required quiz-native-input">
                                        <span class="radio"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
                                        Verdadero
                                    </label>
                                    <label class="quiz-opt">
                                        <input type="radio" name="answer[{{ $count }}]" value="false" class="required quiz-native-input">
                                        <span class="radio"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
                                        Falso
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endif
                    @php $count++; @endphp
                @endforeach

                <div id="quizAnswerError" class="quiz-error d-none-js">
                    @include('customers.includes.icon', ['name' => 'circle-alert']) Debes seleccionar una respuesta para continuar.
                </div>

                <div class="lv-foot quiz-foot">
                    <a id="prev" class="lv-fbtn d-none-js" value="1">
                        <span class="fb-txt"><span class="l">Anterior</span></span>
                    </a>
                    @if ($que_count >= 2)
                        <a id="next" class="lv-fbtn next" value="0" role="button" aria-label="Siguiente pregunta">
                            <span class="fb-txt"><span class="l">Siguiente</span></span>
                        </a>
                    @endif
                    @if ($que_count == 1)
                        <a id="finish" class="lv-fbtn next">
                            <span class="fb-txt"><span class="l">Finalizar</span></span>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    @endif

    @if ($topic->type == 1)
        <div id="question-div">
            <form action="{{ route('customers.quiz.store', $topic->id) }}" method="POST" id="question-form">
                {{ csrf_field() }}

                @php $count = 1; @endphp

                <input type="hidden" id="quiz" name="quiz" value="{{ $quiz->id }}">
                <input type="hidden" name="question_id[{{ $count }}]" value="{{ $questions[0]['id'] }}">

                <div id="more_quiz0">
                    <div class="quiz-step" id="quiz1">
                        <div class="quiz-qhead">
                            <div class="quiz-q">{{ $questions[0]['question'] }}</div>
                            <div class="quiz-count"><span id="quizNumber">{{ $count }}</span>/<span class="total-step">{{ $que_count }}</span></div>
                        </div>
                        <div class="quiz-options">
                            @foreach (['a', 'b', 'c', 'd'] as $opt)
                                <label class="quiz-opt">
                                    <input type="checkbox" name="answer[{{ $count }}][]" value="{{ $opt }}" class="required quiz-native-input">
                                    <span class="radio"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
                                    {{ $questions[0][$opt] }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                @foreach ($questions as $key => $question)
                    @if ($key > 0)
                        <div class="d-none-js" id="more_quiz{{ $key }}">
                            <div class="quiz-step" id="quiz{{ $key + 1 }}">
                                <input type="hidden" name="question_id[{{ $count }}]" value="{{ $question['id'] }}">
                                <div class="quiz-qhead">
                                    <div class="quiz-q">{{ $question['question'] }}</div>
                                    <div class="quiz-count"><span id="quizNumber">{{ $count }}</span>/<span class="total-step">{{ $que_count }}</span></div>
                                </div>
                                <div class="quiz-options">
                                    @foreach (['a', 'b', 'c', 'd'] as $opt)
                                        <label class="quiz-opt">
                                            <input type="checkbox" name="answer[{{ $count }}][]" value="{{ $opt }}" class="required quiz-native-input">
                                            <span class="radio"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
                                            {{ $question[$opt] }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                    @php $count++; @endphp
                @endforeach

                <div id="quizAnswerError" class="quiz-error d-none-js">
                    @include('customers.includes.icon', ['name' => 'circle-alert']) Debes seleccionar una respuesta para continuar.
                </div>

                <div class="lv-foot quiz-foot">
                    <a id="prev" class="lv-fbtn d-none-js" value="1">
                        <span class="fb-txt"><span class="l">Anterior</span></span>
                    </a>
                    @if ($que_count >= 2)
                        <a id="next" class="lv-fbtn next" value="0" role="button" aria-label="Siguiente pregunta">
                            <span class="fb-txt"><span class="l">Siguiente</span></span>
                        </a>
                    @endif
                    @if ($que_count == 1)
                        <a id="finish" class="lv-fbtn next">
                            <span class="fb-txt"><span class="l">Finalizar</span></span>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    @endif

</div>

{{-- Sin navegación anterior/siguiente de LECCIONES aquí a propósito: mientras se
     responde el quiz, esta vista nunca representa un quiz "completado" (el
     submit real hace POST -> redirect a quiz-result.blade.php), así que ese
     bloque solo duplicaba visualmente el Anterior/Siguiente de PREGUNTAS de
     abajo (.quiz-foot) con la misma clase .lv-fbtn, confundiendo cuál era
     cuál. La navegación entre lecciones del curso ya vive, correctamente,
     en quiz-result.blade.php una vez el usuario termina el quiz. --}}
