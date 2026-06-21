@extends('layouts.customers')

@section('title', "$course->title")

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

@php
    $certifierThumbnail = optional($course->certifier)->getFirstMedia('thumbnail');
    $courseThumbnail = $course->getFirstMedia('thumbnail');

    $progress = $inscription->progress();
    $last = $progress->first();
    $lastchapter = $last == null ? null : $last->chapter_id;
    $lastlesson = $last == null ? null : $last->lesson_id;
    $percent = $inscription->percent;
    $completedClass = $progress->count();
    $totalClass = $lessions->count();
    $progressPercentage = $totalClass > 0 ? round($completedClass * 100 / $totalClass) : 0;

    $firstLesson = optional($chapters->first())->lessons->first() ?? null;
    $startHref = null;
    if ($firstLesson) {
        $startHref = $firstLesson->type->slug == 'quiz'
            ? route('customers.courses.quiz', $firstLesson->id)
            : route('customers.courses.lesion', $firstLesson->id);
    }

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

    {{-- ===================== VERSIÓN 2 — rail a la izquierda ===================== --}}
    <div class="lv">
        <div class="lv-shell">

            {{-- ===== Rail de capítulos / lecciones ===== --}}
            <aside class="lv-rail {{ $inscription->expire == 1 ? 'd-none' : '' }}">
                <div class="lv-rail-head">
                    <div class="rc">{{ Str::ucfirst(Str::lower($course->categorie?->title ?? 'Curso')) }}</div>
                    <div class="t">Contenido del curso</div>
                    <div class="bar" role="progressbar" aria-valuenow="{{ $progressPercentage }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ $progressPercentage }}% completado"><i style="--pct: {{ $progressPercentage }}%" aria-hidden="true"></i></div>
                    <div class="sub"><b>{{ $completedClass }} de {{ $totalClass }}</b> clases · {{ $progressPercentage }}% completado</div>
                </div>

                @if($chapters->isNotEmpty())
                    @foreach ($chapters as $chapter)
                        @php
                            $progress = $inscription->progress();
                            $progresschapters = $progress->chapters($chapter->id)->count();
                            $countchapter = $chapter->lessons()->count();
                            $isCurrentChapter = $chapter->id == $lastchapter;
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
                                        $validate = App\Models\Course\CourseProgress::validate($lesson->id, $inscription->id, Auth::user()->id);
                                        $isCurrent = $lastlesson == $lesson->id && $percent < 100;
                                        $clickable = $validate == 1 || $counter == 0 || $isCurrent;
                                        $href = $lesson->type->slug == 'quiz'
                                            ? route('customers.courses.quiz', $lesson->id)
                                            : route('customers.courses.lesion', $lesson->id);
                                        $icon = $typeIcons[$lesson->type->slug] ?? 'fa-circle-play';
                                        $label = $typeLabels[$lesson->type->slug] ?? 'Clase';
                                    @endphp

                                    <a class="lv-lesson {{ $isCurrent ? 'active' : '' }} {{ ! $clickable ? 'pe-none' : '' }}"
                                       href="{{ $clickable ? $href : 'javascript:void(0)' }}"
                                       @unless ($clickable) aria-disabled="true" tabindex="-1" aria-label="Lección bloqueada" @endunless>
                                        <span class="lv-check {{ $validate == 1 ? 'done' : '' }}">
                                            @if ($validate == 1)
                                                <i class="fa-solid fa-check" aria-hidden="true"></i>
                                            @elseif (! $clickable)
                                                <i class="fa-solid fa-lock" aria-hidden="true"></i>
                                            @endif
                                        </span>
                                        <span class="li-ic"><i class="fa-duotone {{ $icon }}"></i></span>
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
                    @elseif ($exam->score >= 80)
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

            {{-- ===== Contenido principal (banner + info) ===== --}}
            <main class="lv-main">
                <div class="lp-video">
                    <img src="{{ $courseThumbnail ? $courseThumbnail->getFullUrl() : '/pages/images/courses/default.jpg' }}" alt="{{ $course->title }}" loading="lazy" onerror="this.onerror=null;this.src='/pages/images/courses/default.jpg';">
                    <span class="lp-tag"><i class="fa-solid fa-circle-play"></i> {{ Str::ucfirst(Str::lower($course->categorie?->title ?? 'Curso')) }}</span>
                </div>

                <div class="lv-content">
                    <div class="lv-lhead">
                        <div>
                            <span class="lk"><i class="fa-solid fa-book-open"></i> Curso</span>
                            <h1>{{ $course->title }}</h1>
                            <div class="lmeta">
                                <span><i class="fa-solid fa-list-check"></i> {{ $totalClass }} clases</span>
                                <span><i class="fa-solid fa-chart-simple"></i> {{ $progressPercentage }}% completado</span>
                            </div>
                        </div>
                        @if ($inscription->expire == 0 && $startHref)
                            <a class="lv-markbtn" href="{{ $startHref }}">
                                <i class="fa-solid fa-circle-play"></i> {{ $completedClass > 0 ? 'Continuar curso' : 'Comenzar curso' }}
                            </a>
                        @endif
                    </div>

                    <ul class="nav nav-pills lv-tabs px-0" id="lv-pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="lv-tab active" id="lv-account-tab" data-bs-toggle="pill" data-bs-target="#lv-account" type="button" role="tab">Información</button>
                        </li>
                        <li class="nav-item {{ count($announsments) == 0 ? 'd-none' : '' }}" role="presentation">
                            <button class="lv-tab" id="lv-bills-tab" data-bs-toggle="pill" data-bs-target="#lv-bills" type="button" role="tab">Anuncios</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="lv-tab" id="lv-security-tab" data-bs-toggle="pill" data-bs-target="#lv-security" type="button" role="tab">Certificador</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="lv-pills-tabContent">
                        <div class="tab-pane fade show active" id="lv-account" role="tabpanel">
                            <div class="lv-pane">
                                @if ($course->short != null)
                                    <h3>De qué trata este curso</h3>
                                    {!! $course->short !!}
                                @endif
                                @if ($course->learn != null)
                                    <h3 class="spaced">¿Qué aprenderás?</h3>
                                    {!! $course->learn !!}
                                @endif
                            </div>
                        </div>
                        <div class="tab-pane fade" id="lv-bills" role="tabpanel">
                            <div class="lv-pane">
                                @foreach ($announsments as $announsment)
                                    <h3>{{ $announsment->title }}</h3>
                                    <p>{!! $announsment->description !!}</p>
                                @endforeach
                            </div>
                        </div>
                        <div class="tab-pane fade" id="lv-security" role="tabpanel">
                            <div class="lv-pane">
                                <div class="lv-res">
                                    <span class="ic"><i class="fa-duotone fa-user-graduate"></i></span>
                                    <div class="info">
                                        @if($course->certifier)
                                            <b>{{ $course->certifier->firstname . ' ' . $course->certifier->lastname }}</b>
                                            <span>{{ $course->certifier->profession }}</span>
                                        @endif
                                    </div>
                                </div>
                                @if($course->certifier && $course->certifier->description != null)
                                    <div class="lv-pane px-0 pt-3">{!! $course->certifier->description !!}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($certificate && $exam && $exam->score >= 80)
                        <div class="lv-pane">
                            <a class="examen-card ready" href="{{ route('customers.certificate.download', $certificate->slack) }}" target="_blank">
                                <span class="ex-ic"><i class="fa-duotone fa-award"></i></span>
                                <span class="ex-info"><b>¡Felicidades por alcanzar tu objetivo!</b><span>Haz clic aquí para descargar tu certificado.</span></span>
                                <span class="ex-arrow"><i class="fa-solid fa-download"></i></span>
                            </a>
                        </div>
                    @endif
                </div>
            </main>

        </div>
    </div>

@else

    {{-- ===================== VERSIÓN 1 — sidebar a la derecha ===================== --}}
    <div class="aula">
        <div class="aula-grid" style="{{ $inscription->expire == 1 ? 'grid-template-columns:1fr;' : '' }}">

            {{-- ===== Columna principal ===== --}}
            <div class="lesson-panel">
                <div class="lp-video">
                    <img src="{{ $courseThumbnail ? $courseThumbnail->getFullUrl() : '/pages/images/courses/default.jpg' }}" alt="{{ $course->title }}" loading="lazy" onerror="this.onerror=null;this.src='/pages/images/courses/default.jpg';">
                    <span class="lp-tag"><i class="fa-solid fa-circle-play"></i> {{ Str::ucfirst(Str::lower($course->categorie?->title ?? 'Curso')) }}</span>
                </div>

                <div class="lp-body">
                    <h2 class="lp-title">{{ $course->title }}</h2>

                    <ul class="nav nav-pills lv-tabs px-0 mt-3" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="lv-tab active" id="pills-account-tab" data-bs-toggle="pill" data-bs-target="#pills-account" type="button" role="tab">Información</button>
                        </li>
                        <li class="nav-item {{ count($announsments) == 0 ? 'd-none' : '' }}" role="presentation">
                            <button class="lv-tab" id="pills-bills-tab" data-bs-toggle="pill" data-bs-target="#pills-bills" type="button" role="tab">Anuncios</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="lv-tab" id="pills-security-tab" data-bs-toggle="pill" data-bs-target="#pills-security" type="button" role="tab">Certificador</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="pills-account" role="tabpanel">
                            <div class="lv-pane px-0">
                                @if ($course->short != null)
                                    <h3>De qué trata este curso</h3>
                                    {!! $course->short !!}
                                @endif
                                @if ($course->learn != null)
                                    <h3 class="spaced">¿Qué aprenderás?</h3>
                                    {!! $course->learn !!}
                                @endif
                            </div>
                        </div>
                        <div class="tab-pane fade" id="pills-bills" role="tabpanel">
                            <div class="lv-pane px-0">
                                @foreach ($announsments as $announsment)
                                    <h3>{{ $announsment->title }}</h3>
                                    <p>{!! $announsment->description !!}</p>
                                @endforeach
                            </div>
                        </div>
                        <div class="tab-pane fade" id="pills-security" role="tabpanel">
                            <div class="lv-pane px-0">
                                <div class="lv-res">
                                    <span class="ic"><i class="fa-duotone fa-user-graduate"></i></span>
                                    <div class="info">
                                        @if($course->certifier)
                                            <b>{{ $course->certifier->firstname . ' ' . $course->certifier->lastname }}</b>
                                            <span>{{ $course->certifier->profession }}</span>
                                        @endif
                                    </div>
                                </div>
                                @if($course->certifier && $course->certifier->description != null)
                                    <div class="lv-pane px-0 pt-3">{!! $course->certifier->description !!}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($inscription->expire == 0 && $startHref)
                        <div class="lp-nav">
                            <span class="lp-navnote">{{ $completedClass > 0 ? 'Retoma donde lo dejaste' : 'Empieza tu aprendizaje ahora' }}</span>
                            <a class="lv-markbtn" href="{{ $startHref }}">
                                <i class="fa-solid fa-circle-play"></i> {{ $completedClass > 0 ? 'Continuar curso' : 'Comenzar curso' }}
                            </a>
                        </div>
                    @endif

                    @if ($certificate && $exam && $exam->score >= 80)
                        <a class="examen-card ready mt-3" href="{{ route('customers.certificate.download', $certificate->slack) }}" target="_blank">
                            <span class="ex-ic"><i class="fa-duotone fa-award"></i></span>
                            <span class="ex-info"><b>¡Felicidades por alcanzar tu objetivo!</b><span>Haz clic aquí para descargar tu certificado.</span></span>
                            <span class="ex-arrow"><i class="fa-solid fa-download"></i></span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- ===== Sidebar ===== --}}
            <aside class="aula-side {{ $inscription->expire == 1 ? 'd-none' : '' }}">

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
                            $progress = $inscription->progress();
                            $progresschapters = $progress->chapters($chapter->id)->count();
                            $countchapter = $chapter->lessons()->count();
                            $isCurrentChapter = $chapter->id == $lastchapter;
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
                                            $validate = App\Models\Course\CourseProgress::validate($lesson->id, $inscription->id, Auth::user()->id);
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
                        @elseif ($exam->score >= 80)
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

        </div>
    </div>

@endif
@endsection
