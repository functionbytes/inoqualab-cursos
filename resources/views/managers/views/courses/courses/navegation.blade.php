@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => $course->title])
@endsection
        @section('content')

            <div class="container-fluid">

                <div class="row justify-content-center navegation-content">
                    <div class="col-lg-12 text-center">
                        <span class="fw-bolder text-uppercase fs-2 d-block mb-1">CURSO</span>
                            <h3 class="fw-bolder mb-0 fs-8 lh-base">{{ $course->title }}</h3>
                    </div>
                </div>
                <div class="row justify-content-center mt--20">
                    <div class="col-sm-6 col-lg-4">
                        <a class="card" href="{{ route('manager.courses.chapters', $course->slack) }}">
                            <div class="card-body text-center">
                                <span class="fw-bolder text-uppercase fs-2 d-block mb-7">Opción</span>
                                <div class="my-4">
                                    <img src="/managers/images/courses/chapters.svg" alt="" class="img-fluid" width="110" >
                                </div>
                                <h4 class="fw-bolder  text-uppercase mb-3">Temas</h4>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <a class="card" href="{{ route('manager.courses.lessons', $course->slack) }}">
                            <div class="card-body text-center">
                                <span class="fw-bolder text-uppercase fs-2 d-block mb-7">Opción</span>
                                <div class="my-4">
                                    <img src="/managers/images/courses/class.svg" alt="" class="img-fluid" width="110" >
                                </div>
                                <h4 class="fw-bolder  text-uppercase mb-3">Clases</h4>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <a class="card" href="{{ route('manager.courses.announcements', $course->slack) }}">
                            <div class="card-body text-center">
                                <span class="fw-bolder text-uppercase fs-2 d-block mb-7">Opción</span>
                                <div class="my-4">
                                    <img src="/managers/images/courses/announcements.svg" alt="" class="img-fluid" width="110" >
                                </div>
                                <h4 class="fw-bolder  text-uppercase mb-3">Anuncios</h4>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <a class="card" href="{{ route('manager.courses.quiz', $course->slack) }}">
                            <div class="card-body text-center">
                                <span class="fw-bolder text-uppercase fs-2 d-block mb-7">Opción</span>
                                <div class="my-4">
                                    <img src="/managers/images/courses/quiz.svg" alt="" class="img-fluid" width="110" >
                                </div>
                                <h4 class="fw-bolder  text-uppercase mb-3">Quiz</h4>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <a class="card" href="{{ route('manager.courses.exam', $course->slack) }}">
                            <div class="card-body text-center">
                                <span class="fw-bolder text-uppercase fs-4 d-block mb-7">Opción</span>
                                <div class="my-4">
                                    <img src="/managers/images/courses/exam.svg" alt="" class="img-fluid" width="110" >
                                </div>
                                <h4 class="fw-bolder  text-uppercase mb-3">Examen</h4>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
@endsection

