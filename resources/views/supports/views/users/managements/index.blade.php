@extends('layouts.managers')

@section('content')

    <div class="container-fluid">

        <div class="row justify-content-center navegation-content">
            <div class="col-lg-12 text-center">
                <span class="fw-bolder text-uppercase fs-2 d-block mb-1">CURSO {{ $inscription->slack }}</span>
                <h3 class="fw-bolder mb-0 fs-8 lh-base">{{ $course->title }}</h3>
            </div>
        </div>


        <div class="row justify-content-center mt--20">
            <div class="col-sm-6 col-lg-4">
                <a class="card" href="{{ route('support.enterprises.users.managements.progress.view', $inscription->slack) }}">
                    <div class="card-body text-center">
                        <span class="fw-bolder text-uppercase fs-2 d-block mb-7">Opción</span>
                        <div class="my-4">
                            <i class="font-navegation fa-solid fa-bars-progress"></i>
                        </div>
                        <h4 class="fw-bolder  text-uppercase mb-3">Progreso</h4>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-lg-4">
                <a class="card" href="{{ route('support.enterprises.users.managements.quiz.view', $inscription->slack) }}">
                    <div class="card-body text-center">
                        <span class="fw-bolder text-uppercase fs-2 d-block mb-7">Opción</span>
                        <div class="my-4">
                            <i class="font-navegation fa-solid fa-list-check"></i>
                        </div>
                        <h4 class="fw-bolder  text-uppercase mb-3">Quizzes</h4>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-lg-4">
                <a class="card" href="{{ route('support.enterprises.users.managements.exam.view', $inscription->slack) }}">
                    <div class="card-body text-center">
                        <span class="fw-bolder text-uppercase fs-2 d-block mb-7">Opción</span>
                        <div class="my-4">
                            <i class="font-navegation fa-solid fa-graduation-cap"></i>
                        </div>
                        <h4 class="fw-bolder  text-uppercase mb-3">Examen final</h4>
                    </div>
                </a>
            </div>

        </div>
    </div>

@endsection

