<div class="lv-lhead">
    <div>
        <span class="lk"><i class="fa-solid fa-circle-question"></i> Evaluación</span>
        <h1>{{ ucfirst($lesson->title) }}</h1>
        <div class="lmeta">
            <span><i class="fa-solid fa-list-check"></i> {{ $questions->count() }} preg.</span>
            <span><i class="fa-solid fa-layer-group"></i> {{ $lesson->chapter->title ?? $course->title }}</span>
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
                        <div style="display: none;" id="more_quiz{{ $key }}">
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

                <div id="quizAnswerError" class="quiz-error" style="display: none;">
                    <i class="fa-solid fa-circle-exclamation"></i> Debes seleccionar una respuesta para continuar.
                </div>

                <div class="lv-foot quiz-foot">
                    <a id="prev" class="lv-fbtn" value="1" style="display: none;">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span class="fb-txt"><span class="l">Anterior</span></span>
                    </a>
                    @if ($que_count >= 2)
                        <a id="next" class="lv-fbtn next" value="0" role="button" aria-label="Siguiente pregunta">
                            <span class="fb-txt"><span class="l">Siguiente</span></span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    @endif
                    @if ($que_count == 1)
                        <a id="finish" class="lv-fbtn next">
                            <span class="fb-txt"><span class="l">Finalizar</span></span>
                            <i class="fa-solid fa-flag-checkered"></i>
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
                        <div style="display: none;" id="more_quiz{{ $key }}">
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

                <div id="quizAnswerError" class="quiz-error" style="display: none;">
                    <i class="fa-solid fa-circle-exclamation"></i> Debes seleccionar una respuesta para continuar.
                </div>

                <div class="lv-foot quiz-foot">
                    <a id="prev" class="lv-fbtn" value="1" style="display: none;">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span class="fb-txt"><span class="l">Anterior</span></span>
                    </a>
                    @if ($que_count >= 2)
                        <a id="next" class="lv-fbtn next" value="0" role="button" aria-label="Siguiente pregunta">
                            <span class="fb-txt"><span class="l">Siguiente</span></span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    @endif
                    @if ($que_count == 1)
                        <a id="finish" class="lv-fbtn next">
                            <span class="fb-txt"><span class="l">Finalizar</span></span>
                            <i class="fa-solid fa-flag-checkered"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    @endif

</div>

@if (($prevLesson && $prevLesson !== 'true') || ($nextLesson && $nextLesson !== 'true'))
    <div class="lv-foot lesson-nav-foot">
        @if ($prevLesson && $prevLesson !== 'true')
            <a href="{{ $prevLesson->type->slug == 'quiz' ? route('customers.courses.quiz', $prevLesson->id) : route('customers.courses.lesion', $prevLesson->id) }}" class="lv-fbtn" aria-label="Lección anterior del curso">
                <i class="fa-solid fa-arrow-left"></i>
                <span class="fb-txt"><span class="l">Anterior</span><span class="t">{{ ucfirst(Str::lower($prevLesson->title)) }}</span></span>
            </a>
        @else
            <span></span>
        @endif
        @if ($nextLesson && $nextLesson !== 'true')
            <a href="{{ $nextLesson->type->slug == 'quiz' ? route('customers.courses.quiz', $nextLesson->id) : route('customers.courses.lesion', $nextLesson->id) }}" class="lv-fbtn next" aria-label="Siguiente lección del curso">
                <span class="fb-txt"><span class="l">Siguiente</span><span class="t">{{ ucfirst(Str::lower($nextLesson->title)) }}</span></span>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        @else
            <span></span>
        @endif
    </div>
@endif
