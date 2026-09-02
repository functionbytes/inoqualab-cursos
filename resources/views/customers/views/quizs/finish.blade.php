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

{{-- El resultado va DENTRO del mismo contenedor que las preguntas
     (.quiz-wrap, dentro de .lv-quiz / .lp-body según aula_version), no en un
     card aparte (.aula-result suelto): antes finish.blade.php envolvia
     quiz-result en .aula-result directamente sobre .aula-grid/.lv-content,
     un card angosto y centrado que no coincidia con el ancho ni el fondo del
     card de preguntas de quiz.blade.php, dando la sensacion de un modal
     flotante en vez de la misma vista continuando. La navegacion
     Anterior/Siguiente de PREGUNTAS (.quiz-foot, en quiz-questions.blade.php)
     ya no existe en esta vista -- solo queda la de LECCION que trae
     quiz-result.blade.php (.ar-foot) -- por lo que no hay botones de
     navegacion de preguntas que deshabilitar: simplemente no se renderizan.

     El wrapper .aula-result y el .ar-foot ahora los pone el propio partial
     (quiz-result.blade.php), no esta vista: el .ar-foot vive FUERA del card
     .aula-result (antes ambos compartian un solo card, y el pie de
     navegacion heredaba su padding/ancho angosto en vez del ancho completo
     del panel). --}}
@if(setting('aula_version') == '2')
    <div class="lv">
        <div class="lv-shell">
            @include('customers.partials.views.courses.rail')
            <main class="lv-main">
                <div class="lv-content">
                    <div class="lv-quiz">
                        <div class="quiz-wrap">
                            @include('customers.partials.views.quizs.quiz-result')
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
                        @include('customers.partials.views.quizs.quiz-result')
                    </div>
                </div>
            </div>
            @include('customers.partials.views.courses.rail')
        </div>
    </div>
@endif
@endsection

{{-- El estilo de .aula-result dentro de .quiz-wrap (ancho completo, sin card
     propio), el refuerzo del botón .lv-fbtn y la franja .ar-foot viven en
     aula.css: se comparten con quiz.blade.php/exam.blade.php, que inyectan
     este mismo partial por AJAX sin pasar por esta vista. --}}
