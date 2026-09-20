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
    <meta property="og:image" content="{{ asset('images/course/' . $course->preview_image) }}">
    <meta itemprop="image" content="{{ asset('images/course/' . $course->preview_image) }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ asset('images/course/' . $course->preview_image) }}">
    <meta property="twitter:title" content="{{ $course->title }} ">
    <meta property="twitter:description" content="{{ $course->short_detail }}">
    <meta name="twitter:site" content="{{ url()->full() }}" />
    <link rel="canonical" href="{{ url()->full() }}" />
    <meta name="robots" content="all">
@endsection
@push('css')
    <link rel="stylesheet" href="{{ url('/customers/css/aula.css') }}?v={{ @filemtime(public_path('customers/css/aula.css')) ?: 1 }}">
@endpush
@section('content')
@if(setting('aula_version') == '2')
    <div class="lv">
        <div class="lv-shell">
            @include('customers.partials.views.courses.rail')
            <main class="lv-main">
                <div class="lv-content">
                    <div class="lv-quiz">
                        <div class="quiz-wrap">
                            @include('customers.partials.views.exams.exam-questions')
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
@else
    <div class="aula">
        <div class="aula-grid">
            <div class="lesson-panel">
                <div class="lp-body">
                    <div class="quiz-wrap">
                        @include('customers.partials.views.exams.exam-questions')
                    </div>
                </div>
            </div>
            @include('customers.partials.views.courses.rail')
        </div>
    </div>
@endif

@include('customers.partials.views.courses.assessment-exit-guard')

{{-- Mismo patrón visual que assessment-exit-guard (.ax-modal), ver quiz.blade.php --}}
<div class="modal fade" id="examConfirmModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content ax-modal">
            <div class="modal-body">
                <div class="ax-ico" aria-hidden="true">
                    @include('customers.includes.icon', ['name' => 'send'])
                </div>
                <h5 class="ax-title">¿Finalizar y enviar el examen?</h5>
                <p class="ax-text">No podrás cambiar tus respuestas después de enviarlo.</p>
                <div class="ax-actions">
                    <button type="button" class="ax-btn ax-accent" id="examConfirmAccept">Sí, enviar examen</button>
                    <button type="button" class="ax-btn ax-leave" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('customers/css/views/exams/exam.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('customers/js/views/exams/exam.js') }}"></script>
@endpush
