@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => $course->title])
@endsection
        @section('content')

            <div class="container-fluid">

                <div class="row justify-content-center course-nav-grid">
                    <div class="col-sm-6 col-lg-4">
                        <a class="card course-nav-card" href="{{ route('manager.courses.chapters', $course->slack) }}">
                            <div class="card-body text-center">
                                <span class="badge bg-light-secondary text-muted d-inline-block mb-4">{{ $counts['chapters'] }} {{ Str::plural('tema', $counts['chapters']) }}</span>
                                <div class="course-nav-icon">
                                    {!! \App\Html\IconHelper::render('course-topics', 56) !!}
                                </div>
                                <h4 class="fw-bolder text-uppercase mb-3">Temas</h4>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <a class="card course-nav-card" href="{{ route('manager.courses.lessons', $course->slack) }}">
                            <div class="card-body text-center">
                                <span class="badge bg-light-secondary text-muted d-inline-block mb-4">{{ $counts['lessons'] }} {{ Str::plural('clase', $counts['lessons']) }}</span>
                                <div class="course-nav-icon">
                                    {!! \App\Html\IconHelper::render('course-lessons', 56) !!}
                                </div>
                                <h4 class="fw-bolder text-uppercase mb-3">Clases</h4>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <a class="card course-nav-card" href="{{ route('manager.courses.announcements', $course->slack) }}">
                            <div class="card-body text-center">
                                <span class="badge bg-light-secondary text-muted d-inline-block mb-4">{{ $counts['announcements'] }} {{ Str::plural('anuncio', $counts['announcements']) }}</span>
                                <div class="course-nav-icon">
                                    {!! \App\Html\IconHelper::render('course-announcements', 56) !!}
                                </div>
                                <h4 class="fw-bolder text-uppercase mb-3">Anuncios</h4>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <a class="card course-nav-card" href="{{ route('manager.courses.quiz', $course->slack) }}">
                            <div class="card-body text-center">
                                <span class="badge bg-light-secondary text-muted d-inline-block mb-4">{{ $counts['quizs'] }} {{ Str::plural('quiz', $counts['quizs']) }}</span>
                                <div class="course-nav-icon">
                                    {!! \App\Html\IconHelper::render('course-quiz', 56) !!}
                                </div>
                                <h4 class="fw-bolder text-uppercase mb-3">Quiz</h4>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <a class="card course-nav-card" href="{{ route('manager.courses.exam', $course->slack) }}">
                            <div class="card-body text-center">
                                <span class="badge bg-light-secondary text-muted d-inline-block mb-4">{{ $counts['exams'] }} {{ $counts['exams'] === 1 ? 'examen' : 'exámenes' }}</span>
                                <div class="course-nav-icon">
                                    {!! \App\Html\IconHelper::render('course-exam', 56) !!}
                                </div>
                                <h4 class="fw-bolder text-uppercase mb-3">Examen</h4>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/courses/courses/navegation.css') }}">
@endpush
