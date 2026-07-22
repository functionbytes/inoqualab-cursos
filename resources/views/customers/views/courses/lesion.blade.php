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

    // Alias para el partial compartido del rail (customers.partials.views.courses.rail)
    $totalClass = $totalLessons;
    $completedClass = $doneLessons;
    $progressPercentage = $pct;
    $lastlesson = $classing->id;
    $lastchapter = $classing->chapter_id;
    $percent = $percents;
@endphp

@if(setting('aula_version') == '2')

    {{-- ===================== VERSIÓN 2 — rail a la izquierda ===================== --}}
    <div class="lv">
        <div class="lv-shell">
            @include('customers.partials.views.courses.rail')

            <main class="lv-main">
                <div class="lv-content">
                    @include('customers.partials.views.courses.lesson-content')
                </div>
            </main>
        </div>
    </div>

@else

    {{-- ===================== VERSIÓN 1 — sidebar a la derecha ===================== --}}
    <main class="aula">
        <div class="container">

            <div class="aula-head">
                <div class="ah-left">
                    <h1>{{ ucfirst($classing->title) }}</h1>
                    <div class="crumbline">{{ $course->title }} · <b>{{ $classing->chapter->title ?? 'Lección' }}</b></div>
                </div>
            </div>

            <div class="aula-grid">
                <div class="lesson-panel">
                    @include('customers.partials.views.courses.lesson-content')
                </div>
                @include('customers.partials.views.courses.rail')
            </div>
        </div>
    </main>

@endif
@endsection
