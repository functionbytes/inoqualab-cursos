@extends('layouts.customers')
@section('title', "$course->title")
@section('head')
    @php
        $url = URL::current();
    @endphp
    <meta name="title" content="{{ $course->title }}">
    <meta name="description" content="{{ $course->short_detail }} ">
    <meta property="og:title" content="{{ $course->title }} ">
    <meta property="og:url" content="{{ $url }}">
    <meta property="og:description" content="{{ $course->short_detail }}">
    <meta property="og:type" content="website">
    <link rel="canonical" href="{{ url()->full() }}" />
    <meta name="robots" content="all">
@endsection

@push('css')
    <link rel="stylesheet" href="{{ url('/customers/css/aula.css') }}">
@endpush

@section('content')

@php
    $allLessons    = $class->where('available', 1);
    $totalLessons  = $allLessons->count();
    $doneLessons   = count($progress);
    $pct           = $totalLessons > 0 ? min(100, round($doneLessons * 100 / $totalLessons)) : 0;
    $typeSlug      = $classing->type->slug;
    $exam          = $exam ?? null;
@endphp

<main class="aula">
    <div class="container">

        <div class="aula-head">
            <div class="ah-left">
                <a class="aula-back" href="{{ route('customers.courses.content', $course->slack) }}">
                    <i class="fa-solid fa-arrow-left"></i> Volver al curso
                </a>
                <h1>{{ ucfirst($classing->title) }}</h1>
                <div class="crumbline">{{ $course->title }} · <b>{{ $classing->chapter->title ?? 'Lección' }}</b></div>
            </div>
        </div>

        <div class="aula-grid">

            {{-- ===== Panel de lección ===== --}}
            <div class="lesson-panel">

                @if ($typeSlug == 'video' && $classing->url)
                    <div class="lp-video">
                        <span class="lp-tag"><i class="fa-solid fa-circle-play"></i> Clase en video</span>
                        <iframe
                            src="{{ route('customers.courses.player', ['lesson' => $classing->id]) }}"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
                            allowfullscreen></iframe>
                    </div>
                @endif

                <div class="lp-body">
                    @php
                        $kickerIcon = ['video'=>'fa-circle-play','audio'=>'fa-volume','image'=>'fa-image','pdf'=>'fa-file-pdf','zip'=>'fa-file-zipper'][$typeSlug] ?? 'fa-book-open';
                        $kickerLabel = ['video'=>'Video','audio'=>'Audio','image'=>'Imagen','pdf'=>'Documento','zip'=>'Recurso'][$typeSlug] ?? 'Lección';
                    @endphp
                    <span class="lp-kicker"><i class="fa-solid {{ $kickerIcon }}"></i> {{ $kickerLabel }}</span>
                    <h1 class="lp-title">{{ ucfirst($classing->title) }}</h1>

                    @if ($classing->detail)
                        <div class="lp-desc">{!! clean($classing->detail, 'content') !!}</div>
                    @endif

                    @if (in_array($typeSlug, ['audio','image','pdf','zip']) && $classing->hasMedia($typeSlug))
                        @php
                            $fileIcon = ['audio'=>'fa-volume','image'=>'fa-image','pdf'=>'fa-file-pdf','zip'=>'fa-file-zipper'][$typeSlug] ?? 'fa-file';
                            $fileMedia = $classing->getFirstMedia($typeSlug);
                        @endphp
                        <div class="lp-rule"></div>
                        <a class="doc-card" href="{{ $fileMedia->getFullUrl() }}" target="_blank">
                            <div class="ic"><i class="fa-solid {{ $fileIcon }}"></i></div>
                            <div class="info">
                                <b>{{ $fileMedia->file_name }}</b>
                                <span>Material de la lección · {{ strtoupper($typeSlug) }}</span>
                            </div>
                            <span class="dc-dl"><i class="fa-solid fa-download"></i></span>
                        </a>
                    @endif

                    <div class="lp-rule"></div>

                    <div class="lp-nav">
                        <form action="{{ route('customers.courses.prev') }}" method="POST" onsubmit="var b=this.querySelector('button');b.disabled=true;">
                            @csrf
                            <input type="hidden" name="course" value="{{ $course->id }}">
                            <input type="hidden" name="lesson" value="{{ $classing->id }}">
                            <input type="hidden" name="user" value="{{ $user->id }}">
                            <button type="submit" class="nav-circle" aria-label="Lección anterior">
                                <i class="fa-solid fa-arrow-left"></i> Anterior
                            </button>
                        </form>

                        <form action="{{ route('customers.courses.realized') }}" method="POST" onsubmit="var b=this.querySelector('button');b.disabled=true;">
                            @csrf
                            <input type="hidden" name="course" value="{{ $course->id }}">
                            <input type="hidden" name="lesson" value="{{ $classing->id }}">
                            <input type="hidden" name="user" value="{{ $user->id }}">
                            <button type="submit" class="lp-complete" aria-label="Completar y seguir">
                                <i class="fa-solid fa-check"></i> Completar y seguir
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ===== Sidebar ===== --}}
            <aside class="aula-side">

                <div class="side-card avance-card">
                    <div class="h">
                        <span class="t">Tu avance</span>
                        <span class="pct">{{ $pct }}%</span>
                    </div>
                    <div class="progress-track"><div class="progress-fill" style="--pct: {{ $pct }}%"></div></div>
                    <div class="sub">
                        <span>Clases completadas</span>
                        <b>{{ $doneLessons }} / {{ $totalLessons }}</b>
                    </div>
                </div>

                @if ($inscription->expire == 0 && $chapters->isNotEmpty())
                    @php
                        $chapters = $chapters->where('available', 1);
                        $chaptercount = count($chapters);
                        $item = 0;
                    @endphp

                    @foreach ($chapters as $chapter)
                        @php
                            $item++;
                            $progresschapter = $chapterProgress[$chapter->id] ?? 0;
                            // eager-loaded desde el controller — sin queries adicionales
                            $countchapter = $chapter->lessons->count();
                            $chapterLessons = $chapter->lessons;
                            $isCurrentChapter = $classing->chapter->id == $chapter->id;
                        @endphp

                        <div class="side-card lessons-card">
                            <a class="lc-head" data-bs-toggle="collapse" href="#collapse{{ $chapter->id }}" role="button" aria-expanded="{{ $isCurrentChapter ? 'true' : 'false' }}" aria-controls="collapse{{ $chapter->id }}">
                                <div class="t">{{ $chapter->title }}</div>
                                <div class="n">({{ $progresschapter }} / {{ $countchapter }})</div>
                            </a>
                            <div class="collapse {{ $isCurrentChapter ? 'show' : '' }}" id="collapse{{ $chapter->id }}">
                                <div class="lessons-list">
                                    @foreach ($chapterLessons as $lesson)
                                        @php
                                            $validate  = in_array($lesson->id, $completedLessonIds);
                                            $isCurrent = $classing->id == $lesson->id;
                                            $href = $lesson->type->slug == 'quiz'
                                                ? route('customers.courses.quiz', $lesson->id)
                                                : route('customers.courses.lesion', $lesson->id);
                                            $icon = [
                                                'video'=>'fa-video','text'=>'fa-message','audio'=>'fa-volume',
                                                'image'=>'fa-image','pdf'=>'fa-file-pdf','zip'=>'fa-file-zipper','quiz'=>'fa-circle-question'
                                            ][$lesson->type->slug] ?? 'fa-circle-play';
                                            $rowClass = $isCurrent ? 'active' : ($validate ? 'done' : 'locked');
                                            $meta = ['video'=>'Video','text'=>'Lectura','audio'=>'Audio','image'=>'Imagen','pdf'=>'Documento','zip'=>'Recurso','quiz'=>'Quiz'][$lesson->type->slug] ?? 'Lección';
                                        @endphp
                                        <a class="lesson-row {{ $rowClass }}" href="{{ $validate || $isCurrent ? $href : 'javascript:void(0)' }}">
                                            <span class="lr-ic"><i class="fa-solid {{ $icon }}"></i></span>
                                            <span class="lr-main">
                                                <span class="lr-title">{{ ucfirst($lesson->title) }}</span>
                                                <span class="lr-meta">{{ $meta }}</span>
                                            </span>
                                            <span class="lr-status">
                                                @if ($isCurrent)
                                                    <i class="fa-solid fa-circle-play" title="En curso"></i>
                                                @elseif ($validate)
                                                    <i class="fa-solid fa-circle-check" title="Completada"></i>
                                                @else
                                                    <i class="fa-solid fa-lock" title="Bloqueada"></i>
                                                @endif
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Examen final --}}
                    @if ($item == $chaptercount)
                        @if ($exam == null)
                            <div class="side-card examen-card">
                                <span class="ex-ic"><i class="fa-solid fa-graduation-cap"></i></span>
                                <span class="ex-info"><b>Examen final</b><span>Completa todas las clases para habilitarlo</span></span>
                                <span class="ex-arrow"><i class="fa-solid fa-lock"></i></span>
                            </div>
                        @elseif ($exam != null && $percents < 100)
                            <div class="side-card examen-card">
                                <span class="ex-ic"><i class="fa-solid fa-graduation-cap"></i></span>
                                <span class="ex-info"><b>Examen final</b><span>Completa todas las clases para habilitarlo</span></span>
                                <span class="ex-arrow"><i class="fa-solid fa-lock"></i></span>
                            </div>
                        @elseif ($exam != null && $percents >= 100)
                            @if ($exam->score >= 80)
                                <div class="side-card examen-card ready">
                                    <span class="ex-ic"><i class="fa-solid fa-award"></i></span>
                                    <span class="ex-info"><b>Examen final</b><span>¡Aprobado!</span></span>
                                    <span class="ex-arrow"><i class="fa-solid fa-circle-check"></i></span>
                                </div>
                            @else
                                <a class="side-card examen-card ready" href="{{ route('customers.courses.exam', $course->slack) }}">
                                    <span class="ex-ic"><i class="fa-solid fa-graduation-cap"></i></span>
                                    <span class="ex-info"><b>Examen final</b><span>Presentar examen</span></span>
                                    <span class="ex-arrow"><i class="fa-solid fa-arrow-right"></i></span>
                                </a>
                            @endif
                        @endif
                    @endif
                @endif

            </aside>

        </div>
    </div>
</main>
@endsection
