<div class="lv-lhead">
    <div>
        <span class="lk">@include('customers.includes.icon', ['name' => 'grad']) Examen final</span>
        <h1>{{ ucfirst($course->title) }}</h1>
        <div class="lmeta">
            <span>@include('customers.includes.icon', ['name' => 'list-check']) {{ $questions->count() }} preg.</span>
        </div>
    </div>
</div>

<div class="quiz-rule"></div>

<div class="progress-track" aria-label="Progreso">
    <div id="progressbar" class="progress-fill"></div>
</div>

<div id="question_block" class="question-block">

    <input type="hidden" id="type" name="type" value="{{ $topic->type }}">

    @php
        $que_count = $questions->count();
        $count = 1;
    @endphp

    @if ($topic->type == 0)
        <div id="question-div">
            <form action="{{ route('customers.exam.store', $topic->id) }}" method="POST" id="question-form">
                {{ csrf_field() }}

                @php $count = 1; @endphp

                <input type="hidden" id="exam" name="exam" value="{{ $exam->id }}">
                <input type="hidden" name="question_id[{{ $count }}]" value="{{ $questions[0]['id'] }}">

                <div id="more_exam0">
                    <div class="quiz-step" id="exam1">
                        <div class="quiz-qhead">
                            <div class="quiz-q">{{ $questions[0]['question'] }}</div>
                            <div class="quiz-count"><span id="examNumber">{{ $count }}</span>/<span class="total-step">{{ $que_count }}</span></div>
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
                        <div class="d-none-js" id="more_exam{{ $key }}">
                            <div class="quiz-step" id="exam{{ $key + 1 }}">
                                <input type="hidden" name="question_id[{{ $count }}]" value="{{ $question['id'] }}">
                                <div class="quiz-qhead">
                                    <div class="quiz-q">{{ $question['question'] }}</div>
                                    <div class="quiz-count"><span id="examNumber">{{ $count }}</span>/<span class="total-step">{{ $que_count }}</span></div>
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
            <form action="{{ route('customers.exam.store', $topic->id) }}" method="POST" id="question-form">
                {{ csrf_field() }}

                @php $count = 1; @endphp

                <input type="hidden" id="exam" name="exam" value="{{ $exam->id }}">
                <input type="hidden" name="question_id[{{ $count }}]" value="{{ $questions[0]['id'] }}">

                <div id="more_exam0">
                    <div class="quiz-step" id="exam1">
                        <div class="quiz-qhead">
                            <div class="quiz-q">{{ $questions[0]['question'] }}</div>
                            <div class="quiz-count"><span id="examNumber">{{ $count }}</span>/<span class="total-step">{{ $que_count }}</span></div>
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
                        <div class="d-none-js" id="more_exam{{ $key }}">
                            <div class="quiz-step" id="exam{{ $key + 1 }}">
                                <input type="hidden" name="question_id[{{ $count }}]" value="{{ $question['id'] }}">
                                <div class="quiz-qhead">
                                    <div class="quiz-q">{{ $question['question'] }}</div>
                                    <div class="quiz-count"><span id="examNumber">{{ $count }}</span>/<span class="total-step">{{ $que_count }}</span></div>
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

@if ($lastCourseLesson)
    @php
        $lastLessonHref = $lastCourseLesson->type->slug == 'quiz'
            ? route('customers.courses.quiz', $lastCourseLesson->id)
            : route('customers.courses.lesion', $lastCourseLesson->id);
    @endphp
    <div class="lv-foot lesson-nav-foot">
        <a href="{{ $lastLessonHref }}" class="lv-fbtn" aria-label="Volver a la última lección">
            <span class="fb-txt"><span class="l">Anterior</span><span class="t">{{ ucfirst(Str::lower($lastCourseLesson->title)) }}</span></span>
        </a>
        <span></span>
    </div>
@endif
