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

<div class="aula">
    <div class="aula-grid">

        {{-- ===== Panel de lección ===== --}}
        <div class="lesson-panel">

            @if ($typeSlug == 'video' && $classing->url)
                <div class="lp-video">
                    <iframe
                        src="{{ route('customers.courses.player', ['lesson' => $classing->id]) }}"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
                        allowfullscreen></iframe>
                </div>
            @elseif (in_array($typeSlug, ['audio','image','pdf','zip']) && $classing->hasMedia($typeSlug))
                @php
                    $fileIcon = ['audio'=>'fa-volume','image'=>'fa-image','pdf'=>'fa-file-pdf','zip'=>'fa-file-zipper'][$typeSlug] ?? 'fa-file';
                @endphp
                <div class="lp-file">
                    <div class="ic"><i class="fa-duotone {{ $fileIcon }}"></i></div>
                    <p>Descarga el material de esta lección</p>
                    <a class="dl" href="{{ $classing->getfirstMedia($typeSlug)->getfullUrl() }}" target="_blank">
                        <i class="fa-solid fa-download"></i> Descargar contenido
                    </a>
                </div>
            @endif

            <div class="lp-body">
                <span class="lp-kicker"><i class="fa-solid fa-circle-play"></i> {{ $classing->chapter->title ?? 'Lección' }}</span>
                <h1 class="lp-title">{{ ucfirst($classing->title) }}</h1>

                @if ($classing->detail)
                    <div class="lp-desc">{!! $classing->detail !!}</div>
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
                        <button type="submit" class="nav-circle next" aria-label="Siguiente lección">
                            Completar y seguir <i class="fa-solid fa-arrow-right"></i>
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
                <div class="progress-track"><div class="progress-fill" style="width: {{ $pct }}%"></div></div>
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

                    <div class="mod-card">
                        <a class="mod-head" data-bs-toggle="collapse" href="#collapse{{ $chapter->id }}" role="button" aria-expanded="{{ $isCurrentChapter ? 'true' : 'false' }}" aria-controls="collapse{{ $chapter->id }}">
                            <div class="mt">
                                <span class="tt">{{ $chapter->title }}</span>
                                <span class="ss">{{ $progresschapter }} / {{ $countchapter }} completadas</span>
                            </div>
                            <span class="chev"><i class="fa-solid fa-chevron-down"></i></span>
                        </a>
                        <div class="collapse mod-body {{ $isCurrentChapter ? 'show' : '' }}" id="collapse{{ $chapter->id }}">
                            @foreach ($chapterLessons as $lesson)
                                @php
                                    $validate  = in_array($lesson->id, $completedLessonIds);
                                    $isCurrent = $classing->id == $lesson->id;
                                    $href = $lesson->type->slug == 'quiz'
                                        ? route('customers.courses.quiz', $lesson->id)
                                        : route('customers.courses.lesion', $lesson->id);
                                    $icon = [
                                        'video'=>'fa-video','text'=>'fa-message-lines','audio'=>'fa-volume',
                                        'image'=>'fa-image','pdf'=>'fa-file-pdf','zip'=>'fa-file-zipper','quiz'=>'fa-hexagon-check'
                                    ][$lesson->type->slug] ?? 'fa-circle-play';
                                    $rowClass = $validate ? 'done' : ($isCurrent ? 'view' : 'locked');
                                @endphp
                                <a class="lrow {{ $rowClass }}" href="{{ $validate || $isCurrent ? $href : 'javascript:void(0)' }}">
                                    <span class="li-ic"><i class="fa-duotone {{ $icon }}"></i></span>
                                    <span class="li-title">{{ ucfirst($lesson->title) }}</span>
                                    <span class="li-status">
                                        @if ($validate)
                                            <i class="fa-solid fa-circle-check done" title="Completada"></i>
                                        @elseif ($isCurrent)
                                            <i class="fa-solid fa-circle-play cur" title="En curso"></i>
                                        @else
                                            <i class="fa-solid fa-lock lock" title="Bloqueada"></i>
                                        @endif
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                {{-- Examen final --}}
                @if ($item == $chaptercount)
                    @if ($exam == null)
                        <div class="exam-card locked">
                            <span class="ex-ic"><i class="fa-duotone fa-graduation-cap"></i></span>
                            <span class="ex-info"><b>Examen final</b><span>Completa todas las clases para habilitarlo</span></span>
                            <span class="ex-arrow"><i class="fa-solid fa-lock"></i></span>
                        </div>
                    @elseif ($exam != null && $percents < 100)
                        <div class="exam-card locked">
                            <span class="ex-ic"><i class="fa-duotone fa-graduation-cap"></i></span>
                            <span class="ex-info"><b>Examen final</b><span>Completa todas las clases para habilitarlo</span></span>
                            <span class="ex-arrow"><i class="fa-solid fa-lock"></i></span>
                        </div>
                    @elseif ($exam != null && $percents >= 100)
                        @if ($exam->score >= 80)
                            <div class="exam-card done">
                                <span class="ex-ic"><i class="fa-duotone fa-award"></i></span>
                                <span class="ex-info"><b>Examen final</b><span>¡Aprobado!</span></span>
                                <span class="ex-arrow"><i class="fa-solid fa-circle-check"></i></span>
                            </div>
                        @else
                            <a class="exam-card ready" href="{{ route('customers.courses.exam', $course->slack) }}">
                                <span class="ex-ic"><i class="fa-duotone fa-graduation-cap"></i></span>
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
@endsection
