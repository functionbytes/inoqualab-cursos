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
@endphp

<div class="aula">
    <div class="aula-grid" style="{{ $inscription->expire == 1 ? 'grid-template-columns:1fr;' : '' }}">

        {{-- ===== Columna principal ===== --}}
        <div>
            <div class="hub-card">
                <div class="hub-banner">
                    <img src="{{ $courseThumbnail ? $courseThumbnail->getFullUrl() : '/pages/images/courses/default.jpg' }}" alt="{{ $course->title }}" loading="lazy" onerror="this.onerror=null;this.src='/pages/images/courses/default.jpg';">
                    <span class="cat">{{ Str::ucfirst(Str::lower($course->categorie->title)) }}</span>
                    <h1>{{ $course->title }}</h1>
                </div>

                <ul class="nav nav-pills hub-tabs" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pills-account-tab" data-bs-toggle="pill" data-bs-target="#pills-account" type="button" role="tab">Información</button>
                    </li>
                    <li class="nav-item {{ count($announsments) == 0 ? 'd-none' : '' }}" role="presentation">
                        <button class="nav-link" id="pills-bills-tab" data-bs-toggle="pill" data-bs-target="#pills-bills" type="button" role="tab">Anuncios</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-security-tab" data-bs-toggle="pill" data-bs-target="#pills-security" type="button" role="tab">Certificador</button>
                    </li>
                </ul>

                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-account" role="tabpanel">
                        <div class="hub-pane">
                            @if ($course->short != null)
                                <h3>De qué trata este curso</h3>
                                {!! $course->short !!}
                            @endif
                            @if ($course->learn != null)
                                <div class="pane-sep">
                                    <h3>¿Qué aprenderás?</h3>
                                    {!! $course->learn !!}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-bills" role="tabpanel">
                        <div class="hub-pane">
                            @foreach ($announsments as $announsment)
                                <div class="hub-announce">
                                    <h6>{{ $announsment->title }}</h6>
                                    <p>{!! $announsment->description !!}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-security" role="tabpanel">
                        <div class="hub-pane">
                            <div class="hub-cert">
                                <img src="{{ $certifierThumbnail ? $certifierThumbnail->getFullUrl() : '/pages/images/certifier/default.jpg' }}" alt="" loading="lazy" onerror="this.onerror=null;this.src='/pages/images/certifier/default.jpg';">
                                @if($course->certifier)
                                    <div>
                                        <h5>{{ $course->certifier->firstname . ' ' . $course->certifier->lastname }}</h5>
                                        <span class="prof">{{ $course->certifier->profession }}</span>
                                        @if($course->certifier->description != null)
                                            <p class="desc">{!! $course->certifier->description !!}</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if ($inscription->expire == 0 && $startHref)
                    <div class="hub-cta">
                        <span class="lbl">{{ $completedClass > 0 ? 'Retoma donde lo dejaste' : 'Empieza tu aprendizaje ahora' }}</span>
                        <a class="go" href="{{ $startHref }}">
                            <i class="fa-solid fa-circle-play"></i> {{ $completedClass > 0 ? 'Continuar curso' : 'Comenzar curso' }}
                        </a>
                    </div>
                @endif
            </div>

            @if ($certificate && $exam && $exam->score >= 80)
                <a class="cert-download" href="{{ route('customers.certificate.download', $certificate->slack) }}" target="_blank">
                    <span class="ic"><i class="fa-duotone fa-award"></i></span>
                    <span class="ct">
                        <b>¡Felicidades por alcanzar tu objetivo!</b>
                        <span>Haz clic aquí para descargar tu certificado.</span>
                    </span>
                </a>
            @endif
        </div>

        {{-- ===== Sidebar ===== --}}
        <aside class="aula-side {{ $inscription->expire == 1 ? 'd-none' : '' }}">

            <div class="side-card avance-card">
                <div class="h">
                    <span class="t">Tu avance</span>
                    <span class="pct">{{ $progressPercentage }}%</span>
                </div>
                <div class="progress-track"><div class="progress-fill" style="width: {{ $progressPercentage }}%"></div></div>
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

                    <div class="mod-card">
                        <a class="mod-head" data-bs-toggle="collapse" href="#collapse{{ $chapter->id }}" role="button" aria-expanded="{{ $isCurrentChapter ? 'true' : 'false' }}" aria-controls="collapse{{ $chapter->id }}">
                            <div class="mt">
                                <span class="tt">{{ $chapter->title }}</span>
                                <span class="ss">{{ $progresschapters }} / {{ $countchapter }} completadas</span>
                            </div>
                            <span class="chev"><i class="fa-solid fa-chevron-down"></i></span>
                        </a>
                        <div class="collapse mod-body {{ $isCurrentChapter ? 'show' : '' }}" id="collapse{{ $chapter->id }}">
                            @foreach ($chapter->lessons as $lesson)
                                @php
                                    $validate = App\Models\Course\CourseProgress::validate($lesson->id, $inscription->id, Auth::user()->id);
                                    $isCurrent = $lastlesson == $lesson->id && $percent < 100;
                                    $clickable = $validate == 1 || $counter == 0 || $isCurrent;
                                    $href = $lesson->type->slug == 'quiz'
                                        ? route('customers.courses.quiz', $lesson->id)
                                        : route('customers.courses.lesion', $lesson->id);
                                    $icon = [
                                        'video'=>'fa-video','text'=>'fa-message-lines','audio'=>'fa-volume',
                                        'image'=>'fa-image','pdf'=>'fa-file-pdf','zip'=>'fa-file-zipper','quiz'=>'fa-hexagon-check'
                                    ][$lesson->type->slug] ?? 'fa-circle-play';
                                    $rowClass = $validate == 1 ? 'done' : ($isCurrent ? 'view' : ($clickable ? '' : 'locked'));
                                @endphp
                                <a class="lrow {{ $rowClass }}" href="{{ $clickable ? $href : 'javascript:void(0)' }}">
                                    <span class="li-ic"><i class="fa-duotone {{ $icon }}"></i></span>
                                    <span class="li-title">{{ ucfirst(Str::lower($lesson->title)) }}</span>
                                    <span class="li-status">
                                        @if ($validate == 1)
                                            <i class="fa-solid fa-circle-check done"></i>
                                        @elseif ($isCurrent)
                                            <i class="fa-solid fa-circle-play cur"></i>
                                        @elseif ($clickable)
                                            <i class="fa-solid fa-circle-play cur"></i>
                                        @else
                                            <i class="fa-solid fa-lock lock"></i>
                                        @endif
                                    </span>
                                </a>
                                @php $counter++; @endphp
                            @endforeach
                        </div>
                    </div>
                @endforeach

                {{-- Examen final --}}
                @if ($percent == 100)
                    @if ($exam == null || $percents < 100)
                        <div class="exam-card locked">
                            <span class="ex-ic"><i class="fa-duotone fa-graduation-cap"></i></span>
                            <span class="ex-info"><b>Examen final</b><span>Disponible al completar el curso</span></span>
                            <span class="ex-arrow"><i class="fa-solid fa-lock"></i></span>
                        </div>
                    @elseif ($exam->score >= 80)
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
                @else
                    <div class="exam-card locked">
                        <span class="ex-ic"><i class="fa-duotone fa-graduation-cap"></i></span>
                        <span class="ex-info"><b>Examen final</b><span>Completa todas las clases para habilitarlo</span></span>
                        <span class="ex-arrow"><i class="fa-solid fa-lock"></i></span>
                    </div>
                @endif
            @endif

        </aside>

    </div>
</div>
@endsection
