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
    <link rel="stylesheet" href="{{ url('/customers/css/aula.css') }}?v={{ @filemtime(public_path('customers/css/aula.css')) ?: 1 }}">
@endpush

@section('content')

@php
    $certifierThumbnail = optional($course->certifier)->getFirstMedia('thumbnail');

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
        'video' => 'video', 'text' => 'text', 'audio' => 'audio',
        'image' => 'image', 'pdf' => 'pdf', 'zip' => 'zip', 'quiz' => 'quiz',
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
            @include('customers.partials.views.courses.rail')

            {{-- ===== Contenido principal (banner + info) ===== --}}
            <main class="lv-main">
                <div class="lv-content">
                    {{-- Barra sticky solo-mobile con el toggle del rail (mismo bloque
                         que lesson-content.blade.php): sin este botón .lv-rail-toggle
                         nunca existe en el DOM, el JS de rail.blade.php aborta su bind
                         (if (!$toggles.length) return;) y el rail -- position:fixed y
                         oculto por CSS en mobile -- queda sin forma de abrirse. --}}
                    <div class="lv-mobile-bar">
                        <button type="button" class="lv-rail-toggle" aria-label="Ver clases del curso" aria-expanded="false" aria-controls="lvRail">
                            @include('customers.includes.icon', ['name' => 'menu'])
                        </button>
                    </div>

                    <div class="lv-lhead">
                        <div>
                            <span class="lk">@include('customers.includes.icon', ['name' => 'book']) Curso</span>
                            <h1>{{ $course->title }}</h1>
                            <div class="lmeta">
                                <span>@include('customers.includes.icon', ['name' => 'list-check']) {{ $totalClass }} clases</span>
                                <span>@include('customers.includes.icon', ['name' => 'chart-simple']) {{ $progressPercentage }}% completado</span>
                            </div>
                        </div>
                        @if ($inscription->expire == 0 && $startHref)
                            <a class="lv-markbtn" href="{{ $startHref }}">
                                {{ $completedClass > 0 ? 'Continuar curso' : 'Comenzar curso' }}
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
                                    {!! clean($course->short, 'content') !!}
                                @endif
                                @if ($course->learn != null)
                                    <h3 class="spaced">¿Qué aprenderás?</h3>
                                    <div class="learn-content">{!! clean($course->learn, 'content') !!}</div>
                                @endif
                            </div>
                        </div>
                        <div class="tab-pane fade" id="lv-bills" role="tabpanel">
                            <div class="lv-pane">
                                @foreach ($announsments as $announsment)
                                    <h3>{{ $announsment->title }}</h3>
                                    <p>{!! clean($announsment->description, 'content') !!}</p>
                                @endforeach
                            </div>
                        </div>
                        <div class="tab-pane fade" id="lv-security" role="tabpanel">
                            <div class="lv-pane">
                                @if($course->certifier)
                                    <div class="cert-card">
                                        <div class="cert-head">
                                            <span class="cert-ava">
                                                @if($course->certifier->hasMedia('thumbnail'))
                                                    <img src="{{ $course->certifier->getFirstMediaUrl('thumbnail') }}" alt="{{ $course->certifier->firstname }} {{ $course->certifier->lastname }}">
                                                @else
                                                    @include('customers.includes.icon', ['name' => 'user-grad'])
                                                @endif
                                            </span>
                                            <div class="cert-id">
                                                <b>{{ $course->certifier->firstname . ' ' . $course->certifier->lastname }}</b>
                                                <span>{{ $course->certifier->profession }}</span>
                                            </div>
                                            <span class="cert-badge">@include('customers.includes.icon', ['name' => 'shield-check']) Avala este curso</span>
                                        </div>
                                        @if($course->certifier->description != null)
                                            <div class="cert-creds">{!! clean($course->certifier->description, 'content') !!}</div>
                                        @endif
                                    </div>
                                @else
                                    <div class="lv-empty">
                                        <p>Este curso aún no tiene un certificador asignado.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($certificate)
                        <div class="lv-pane">
                            <a class="examen-card ready" href="{{ route('customers.certificate.download', $certificate->slack) }}" target="_blank">
                                <span class="ex-ic">@include('customers.includes.icon', ['name' => 'award'])</span>
                                <span class="ex-info"><b>¡Felicidades por alcanzar tu objetivo!</b><span>Haz clic aquí para descargar tu certificado.</span></span>
                                <span class="ex-arrow">@include('customers.includes.icon', ['name' => 'download'])</span>
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
        <div class="aula-grid {{ $inscription->expire == 1 ? 'is-single' : '' }}">

            {{-- ===== Columna principal ===== --}}
            <div class="lesson-panel">
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
                                    {!! clean($course->short, 'content') !!}
                                @endif
                                @if ($course->learn != null)
                                    <h3 class="spaced">¿Qué aprenderás?</h3>
                                    <div class="learn-content">{!! clean($course->learn, 'content') !!}</div>
                                @endif
                            </div>
                        </div>
                        <div class="tab-pane fade" id="pills-bills" role="tabpanel">
                            <div class="lv-pane px-0">
                                @foreach ($announsments as $announsment)
                                    <h3>{{ $announsment->title }}</h3>
                                    <p>{!! clean($announsment->description, 'content') !!}</p>
                                @endforeach
                            </div>
                        </div>
                        <div class="tab-pane fade" id="pills-security" role="tabpanel">
                            <div class="lv-pane px-0">
                                <div class="lv-res">
                                    <span class="ic">@include('customers.includes.icon', ['name' => 'user-grad'])</span>
                                    <div class="info">
                                        @if($course->certifier)
                                            <b>{{ $course->certifier->firstname . ' ' . $course->certifier->lastname }}</b>
                                            <span>{{ $course->certifier->profession }}</span>
                                        @endif
                                    </div>
                                </div>
                                @if($course->certifier && $course->certifier->description != null)
                                    <div class="lv-pane px-0 pt-3">{!! clean($course->certifier->description, 'content') !!}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($inscription->expire == 0 && $startHref)
                        <div class="lp-nav">
                            <span class="lp-navnote">{{ $completedClass > 0 ? 'Retoma donde lo dejaste' : 'Empieza tu aprendizaje ahora' }}</span>
                            <a class="lv-markbtn" href="{{ $startHref }}">
                                {{ $completedClass > 0 ? 'Continuar curso' : 'Comenzar curso' }}
                            </a>
                        </div>
                    @endif

                    @if ($certificate)
                        <a class="examen-card ready mt-3" href="{{ route('customers.certificate.download', $certificate->slack) }}" target="_blank">
                            <span class="ex-ic">@include('customers.includes.icon', ['name' => 'award'])</span>
                            <span class="ex-info"><b>¡Felicidades por alcanzar tu objetivo!</b><span>Haz clic aquí para descargar tu certificado.</span></span>
                            <span class="ex-arrow">@include('customers.includes.icon', ['name' => 'download'])</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- ===== Sidebar ===== --}}
            @include('customers.partials.views.courses.rail')

        </div>
    </div>

@endif
@endsection
