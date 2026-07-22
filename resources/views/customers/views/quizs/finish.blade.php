@extends('layouts.customers')

@section('title',$course->title)

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

@if(setting('aula_version') == '2')
    <div class="lv">
        <div class="lv-shell">
            @include('customers.partials.views.courses.rail')
            <main class="lv-main">
                <div class="lv-content">
                    <div class="aula-result">
                        @include('customers.partials.views.quizs.quiz-result')
                    </div>
                </div>
            </main>
        </div>
    </div>
@else
    <div class="aula">
        <div class="aula-grid">
            <div class="aula-result">
                @include('customers.partials.views.quizs.quiz-result')
            </div>
            @include('customers.partials.views.courses.rail')
        </div>
    </div>
@endif
@endsection

@push('css')
<style>
    .ar-foot.lv-foot { padding: 26px 0 0; margin-top: 6px; border-top: 1px solid var(--line); }
    .ar-foot form { display: contents; }
</style>
@endpush
