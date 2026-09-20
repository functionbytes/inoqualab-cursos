@extends('layouts.customers')

@section('title', $course->title )

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

{{-- El wrapper .aula-result y el .ar-foot (fuera de él) los pone el propio
     partial (exam-result.blade.php) -- ver el mismo cambio en
     quiz-result.blade.php/quizs/finish.blade.php. --}}
@if(setting('aula_version') == '2')
    <div class="lv">
        <div class="lv-shell">
            @include('customers.partials.views.courses.rail')
            <main class="lv-main">
                <div class="lv-content">
                    @include('customers.partials.views.exams.exam-result')
                </div>
            </main>
        </div>
    </div>
@else
    <div class="aula">
        <div class="aula-grid">
            {{-- .aula-grid es un grid de 2 columnas (contenido + rail) posicionadas
                 por orden de los hijos directos -- .aula-result y .ar-foot deben
                 quedar juntos en la primera columna, de ahí el wrapper. --}}
            <div>
                @include('customers.partials.views.exams.exam-result')
            </div>
            @include('customers.partials.views.courses.rail')
        </div>
    </div>
@endif

@push('css')
<link rel="stylesheet" href="{{ asset('customers/css/views/exams/finish.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('customers/js/views/exams/finish.js') }}"></script>
@endpush
@endsection
