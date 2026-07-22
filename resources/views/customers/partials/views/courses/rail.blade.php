@php
    $typeIcons = [
        'video' => 'fa-video', 'text' => 'fa-message-lines', 'audio' => 'fa-volume',
        'image' => 'fa-image', 'pdf' => 'fa-file-pdf', 'zip' => 'fa-file-zipper', 'quiz' => 'fa-hexagon-check',
    ];
    $typeLabels = [
        'video' => 'Video', 'text' => 'Lectura', 'audio' => 'Audio',
        'image' => 'Imagen', 'pdf' => 'PDF', 'zip' => 'Recurso', 'quiz' => 'Evaluación',
    ];
@endphp

@if(setting('aula_version') == '2')

    {{-- ===== Rail de capítulos / lecciones (v2) ===== --}}
    <aside class="lv-rail {{ $inscription->expire == 1 ? 'd-none' : '' }}">
        <div class="lv-rail-head">
            @unless(request()->routeIs('customers.courses.content'))
                <a href="{{ route('customers.courses.content', $inscription->slack) }}" class="aula-back rail-back">
                    <i class="fa-solid fa-arrow-left"></i> Volver al curso
                </a>
            @endunless
            <div class="rc">{{ Str::ucfirst(Str::lower($course->categorie?->title ?? 'Curso')) }}</div>
            <div class="t">Contenido del curso</div>
            <div class="bar" role="progressbar" aria-valuenow="{{ $progressPercentage }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ $progressPercentage }}% completado"><i style="--pct: {{ $progressPercentage }}%" aria-hidden="true"></i></div>
            <div class="sub"><b>{{ $completedClass }} de {{ $totalClass }}</b> clases · {{ $progressPercentage }}% completado</div>
        </div>

        @if($chapters->isNotEmpty())
            @foreach ($chapters as $chapter)
                @php
                    $progresschapters = $chapterProgress[$chapter->id] ?? 0;
                    $countchapter = $chapter->lessons->count();
                    // Sin lección "actual" (p. ej. en el examen final, con el curso 100% completado)
                    // se expanden todos los capítulos por defecto en vez de dejarlos todos cerrados.
                    $isCurrentChapter = $chapter->id == $lastchapter || $lastchapter === null;
                    $allDone = $countchapter > 0 && $progresschapters >= $countchapter;
                    $counter = 0;
                @endphp

                <div class="lv-mod">
                    <button class="lv-mod-head {{ $isCurrentChapter ? 'open' : '' }} {{ $allDone ? 'alldone' : '' }}"
                            type="button" data-bs-toggle="collapse" data-bs-target="#lvmod{{ $chapter->id }}"
                            aria-expanded="{{ $isCurrentChapter ? 'true' : 'false' }}" aria-controls="lvmod{{ $chapter->id }}">
                        <span class="mi">
                            @if($allDone)<i class="fa-solid fa-check"></i>@else{{ $loop->iteration }}@endif
                        </span>
                        <span class="mt">
                            <span class="tt">{{ $chapter->title }}</span>
                            <span class="ss">{{ $progresschapters }}/{{ $countchapter }} · {{ $countchapter }} clases</span>
                        </span>
                        <span class="chev"><i class="fa-solid fa-chevron-down"></i></span>
                    </button>

                    <div class="collapse lv-mod-body {{ $isCurrentChapter ? 'show' : '' }}" id="lvmod{{ $chapter->id }}">
                        @foreach ($chapter->lessons as $lesson)
                            @php
                                $validate = in_array($lesson->id, $completedLessonIds);
                                $isCurrent = $lastlesson == $lesson->id && $percent < 100;
                                $clickable = $validate == 1 || $counter == 0 || $isCurrent;
                                $href = $lesson->type->slug == 'quiz'
                                    ? route('customers.courses.quiz', $lesson->id)
                                    : route('customers.courses.lesion', $lesson->id);
                                $icon = $typeIcons[$lesson->type->slug] ?? 'fa-circle-play';
                                $label = $typeLabels[$lesson->type->slug] ?? 'Clase';
                            @endphp

                            <a class="lv-lesson {{ $isCurrent ? 'active' : '' }} {{ $validate == 1 ? 'done' : '' }} {{ ! $clickable ? 'pe-none' : '' }}"
                               href="{{ $clickable ? $href : 'javascript:void(0)' }}"
                               @unless ($clickable) aria-disabled="true" tabindex="-1" aria-label="Lección bloqueada" @endunless>
                                <span class="li-ic">
                                    @if (! $clickable && $validate != 1)
                                        <i class="fa-solid fa-lock" aria-hidden="true"></i>
                                    @else
                                        <i class="fa-duotone {{ $icon }}"></i>
                                    @endif
                                </span>
                                <span class="li-main">
                                    <span class="li-title">{{ ucfirst(Str::lower($lesson->title)) }}</span>
                                    <span class="li-meta">{{ $label }}</span>
                                </span>
                            </a>
                            @php $counter++; @endphp
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif

        {{-- Examen final --}}
        @if ($percent == 100)
            @if ($exam == null || $percents < 100)
                <div class="lv-mod">
                    <div class="examen-card">
                        <span class="ex-ic"><i class="fa-duotone fa-graduation-cap"></i></span>
                        <span class="ex-info"><b>Examen final</b><span>Disponible al completar el curso</span></span>
                        <span class="ex-arrow"><i class="fa-solid fa-lock"></i></span>
                    </div>
                </div>
            @elseif ($certificate)
                <div class="lv-mod">
                    <div class="examen-card">
                        <span class="ex-ic"><i class="fa-duotone fa-award"></i></span>
                        <span class="ex-info"><b>Examen final</b><span>¡Aprobado!</span></span>
                        <span class="ex-arrow"><i class="fa-solid fa-circle-check"></i></span>
                    </div>
                </div>
            @else
                <div class="lv-mod">
                    <a class="examen-card ready" href="{{ route('customers.courses.exam', $course->slack) }}">
                        <span class="ex-ic"><i class="fa-duotone fa-graduation-cap"></i></span>
                        <span class="ex-info"><b>Examen final</b><span>Presentar examen</span></span>
                        <span class="ex-arrow"><i class="fa-solid fa-arrow-right"></i></span>
                    </a>
                </div>
            @endif
        @else
            <div class="lv-mod">
                <div class="examen-card">
                    <span class="ex-ic"><i class="fa-duotone fa-graduation-cap"></i></span>
                    <span class="ex-info"><b>Examen final</b><span>Completa todas las clases para habilitarlo</span></span>
                    <span class="ex-arrow"><i class="fa-solid fa-lock"></i></span>
                </div>
            </div>
        @endif
    </aside>

@else

    {{-- ===== Sidebar de capítulos / lecciones (v1) ===== --}}
    <aside class="aula-side {{ $inscription->expire == 1 ? 'd-none' : '' }}">

        @unless(request()->routeIs('customers.courses.content'))
            <a href="{{ route('customers.courses.content', $inscription->slack) }}" class="aula-back rail-back">
                <i class="fa-solid fa-arrow-left"></i> Volver al curso
            </a>
        @endunless

        <div class="side-card avance-card">
            <div class="h">
                <span class="t">Tu avance</span>
                <span class="pct">{{ $progressPercentage }}%</span>
            </div>
            <div class="progress-track" role="progressbar" aria-valuenow="{{ $progressPercentage }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ $progressPercentage }}% completado"><div class="progress-fill" style="--pct: {{ $progressPercentage }}%"></div></div>
            <div class="sub">
                <span>Clases completadas</span>
                <b>{{ $completedClass }} / {{ $totalClass }}</b>
            </div>
        </div>

        @if($chapters->isNotEmpty())
            @foreach ($chapters as $chapter)
                @php
                    $progresschapters = $chapterProgress[$chapter->id] ?? 0;
                    $countchapter = $chapter->lessons->count();
                    // Sin lección "actual" (p. ej. en el examen final, con el curso 100% completado)
                    // se expanden todos los capítulos por defecto en vez de dejarlos todos cerrados.
                    $isCurrentChapter = $chapter->id == $lastchapter || $lastchapter === null;
                    $counter = 0;
                @endphp

                <div class="side-card lessons-card">
                    <a class="lc-head d-block" data-bs-toggle="collapse" href="#collapse{{ $chapter->id }}" role="button"
                       aria-expanded="{{ $isCurrentChapter ? 'true' : 'false' }}" aria-controls="collapse{{ $chapter->id }}">
                        <div class="t">{{ $chapter->title }}</div>
                        <div class="n">{{ $progresschapters }} / {{ $countchapter }} completadas</div>
                    </a>
                    <div class="collapse {{ $isCurrentChapter ? 'show' : '' }}" id="collapse{{ $chapter->id }}">
                        <div class="lessons-list p-2 d-flex flex-column gap-2">
                            @foreach ($chapter->lessons as $lesson)
                                @php
                                    $validate = in_array($lesson->id, $completedLessonIds);
                                    $isCurrent = $lastlesson == $lesson->id && $percent < 100;
                                    $clickable = $validate == 1 || $counter == 0 || $isCurrent;
                                    $href = $lesson->type->slug == 'quiz'
                                        ? route('customers.courses.quiz', $lesson->id)
                                        : route('customers.courses.lesion', $lesson->id);
                                    $icon = $typeIcons[$lesson->type->slug] ?? 'fa-circle-play';
                                    $label = $typeLabels[$lesson->type->slug] ?? 'Clase';
                                    $rowClass = $validate == 1 ? 'done' : ($isCurrent ? 'active' : '');
                                @endphp
                                <a class="lesson-row {{ $rowClass }} {{ ! $clickable ? 'pe-none' : '' }}"
                                   href="{{ $clickable ? $href : 'javascript:void(0)' }}"
                                   @unless ($clickable) aria-disabled="true" tabindex="-1" aria-label="Lección bloqueada" @endunless>
                                    <span class="lr-ic"><i class="fa-duotone {{ $icon }}" aria-hidden="true"></i></span>
                                    <span class="lr-main">
                                        <span class="lr-title">{{ ucfirst(Str::lower($lesson->title)) }}</span>
                                        <span class="lr-meta">{{ $label }}</span>
                                    </span>
                                    <span class="lr-status">
                                        @if ($validate == 1)
                                            <i class="fa-solid fa-circle-check"></i>
                                        @elseif ($isCurrent)
                                            <i class="fa-solid fa-circle-play"></i>
                                        @elseif ($clickable)
                                            <i class="fa-solid fa-circle-play"></i>
                                        @else
                                            <i class="fa-solid fa-lock"></i>
                                        @endif
                                    </span>
                                </a>
                                @php $counter++; @endphp
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Examen final --}}
            @if ($percent == 100)
                @if ($exam == null || $percents < 100)
                    <div class="side-card examen-card">
                        <span class="ex-ic"><i class="fa-duotone fa-graduation-cap"></i></span>
                        <span class="ex-info"><b>Examen final</b><span>Disponible al completar el curso</span></span>
                        <span class="ex-arrow"><i class="fa-solid fa-lock"></i></span>
                    </div>
                @elseif ($certificate)
                    <div class="side-card examen-card">
                        <span class="ex-ic"><i class="fa-duotone fa-award"></i></span>
                        <span class="ex-info"><b>Examen final</b><span>¡Aprobado!</span></span>
                        <span class="ex-arrow"><i class="fa-solid fa-circle-check"></i></span>
                    </div>
                @else
                    <a class="side-card examen-card ready" href="{{ route('customers.courses.exam', $course->slack) }}">
                        <span class="ex-ic"><i class="fa-duotone fa-graduation-cap"></i></span>
                        <span class="ex-info"><b>Examen final</b><span>Presentar examen</span></span>
                        <span class="ex-arrow"><i class="fa-solid fa-arrow-right"></i></span>
                    </a>
                @endif
            @else
                <div class="side-card examen-card">
                    <span class="ex-ic"><i class="fa-duotone fa-graduation-cap"></i></span>
                    <span class="ex-info"><b>Examen final</b><span>Completa todas las clases para habilitarlo</span></span>
                    <span class="ex-arrow"><i class="fa-solid fa-lock"></i></span>
                </div>
            @endif
        @endif

    </aside>

@endif

@push('scripts')
<script>
    $(function () {
        var $active = $('.lv-lesson.active, .lesson-row.active').first();
        if ($active.length) {
            var $rail = $active.closest('.lv-rail, .aula-side');
            if ($rail.length) {
                var target = $active.offset().top - $rail.offset().top + $rail.scrollTop() - ($rail.innerHeight() / 2) + ($active.outerHeight() / 2);
                $rail.scrollTop(Math.max(0, target));
            }
        }
    });
</script>
@endpush
