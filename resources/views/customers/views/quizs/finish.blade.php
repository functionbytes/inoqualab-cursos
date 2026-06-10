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

<div class="aula">
    <div class="aula-result">
        <span class="ar-badge">Resultado del quiz</span>
        <h2 class="ar-title">{{ Str::upper($lesson->title) }}</h2>

        @if ($score >= $passingScore)
            <div class="ar-icon ok"><i class="fa-solid fa-check"></i></div>
            <p class="ar-score">¡Aprobaste el quiz!</p>
            <p class="ar-sub">Respondiste correctamente {{ $correct > 0 ? $correct : 0 }} de {{ $count > 0 ? $count : 0 }} preguntas. Continúa con la siguiente lección.</p>
            <div class="ar-actions">
                <form action="{{ route('customers.quiz.realized') }}" method="POST">
                    @csrf
                    <input type="hidden" name="course" value="{{ $course->id }}">
                    <input type="hidden" name="lesson" value="{{ $lesson->id }}">
                    <input type="hidden" name="user" value="{{ $user->id }}">
                    <button type="submit" class="ar-primary"><i class="fa-solid fa-arrow-right"></i> Continuar</button>
                </form>
            </div>
        @else
            <div class="ar-icon no"><i class="fa-solid fa-xmark"></i></div>
            <p class="ar-score">No superaste el quiz</p>
            <p class="ar-sub">Acertaste {{ $correct > 0 ? $correct : 0 }} de {{ $count > 0 ? $count : 0 }} preguntas.
                @if ($topic->quiz_again >= 1)Inténtalo nuevamente.@else Revisa el contenido de la lección.@endif Necesitas {{ $topic->per_q_mark }} de {{ $count }} correctas.
            </p>
            <div class="ar-actions">
                @if ($topic->quiz_again >= 1)
                    <a class="ar-primary" href="{{ route('customers.quiz.tryagain', $quiz->id) }}"><i class="fa-solid fa-rotate-right"></i> Reintentar</a>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
