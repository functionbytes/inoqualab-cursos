@php use App\Models\Course\CourseProgress;
 @endphp

@extends('layouts.managers')

@section('page_header')
    @include('distributors.includes.card', ['title' => "Progreso - " . $course->title ])
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12">
            <!-- Yearly Breakup -->
            <div class="card overflow-hidden">
                <div class="card-body">
                    <div class="row align-items-center">
                            <h5 class="card-title mb-9 fw-semibold">CLASES </h5>
                            <h4 class="fw-semibold mb-3">{{ count($class) }}</h4>

                            <div id="customers"></div>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12">
            <!-- Yearly Breakup -->
            @php

                $total_class = $class;
                $total_count = count($total_class);

                $total_per = 100;
                // La formula estaba invertida (total/read en vez de read/total):
                // con progreso parcial normal (3 de 16 lecciones) daba 533% en
                // vez de 19%. Ademas, la inscripcion es la fuente de verdad de
                // si el curso esta culminado: algunas inscripciones antiguas
                // solo tienen un registro "resumen" en course_progress sin
                // detalle por leccion.
                $read_count = \App\Models\Course\CourseProgress::where('inscription_id', $inscription->id)
                    ->where('user_id', $user->id)
                    ->where('culminated', 1)
                    ->whereNotNull('lesson_id')
                    ->count();

                if ($inscription->culminated == 1) {
                    $progres = 100;
                } elseif ($total_count == 0) {
                    $progres = 0;
                } else {
                    $progres = ($read_count / $total_count) * $total_per;
                }

            @endphp


            <div class="card overflow-hidden">
                <div class="card-body">
                    <div class="row align-items-center">
                            <h5 class="card-title mb-9 fw-semibold">PORCENTAJE</h5>
                            <h4 class="fw-semibold mb-3">{{ round($progres)  }}%</h4>

                            <div id="customers1"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="col-md-12 col-lg-12">
        @php
            $chapters = $class->groupBy('chapter_id');

        @endphp

        <div class="row">
            @foreach ($chapters->sortBy('position') as $key => $chapter)
                <div class="col-md-6 col-lg-12">
                    <div class="card w-100">
                        <div class="card-header border-bottom">
                            <h6 class="mb-1 fw-bold">{{ $chapter->first()->chapter->title }}</h6>
                            <p class="text-muted small mb-0">Detalle del curso y seguimiento del progreso</p>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table align-middle text-nowrap mb-0">
                                    <thead>

                                    </thead>
                                    <tbody class="border-top">

                                    @foreach ($chapter as $key => $class)

                                        @php
                                        $validate = App\Models\Course\CourseProgress::validate($class->id,$inscription->id,$user->id) || $inscription->culminated == 1;
                                        @endphp

                                        <tr>
                                            <td class="ps-0">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <h6 class="fw-semibold mb-1">{{ $class->title }}</h6>
                                                        <p class="fs-2 mb-0 text-muted">{{ $class->chapter->title }}</p>
                                                    </div>
                                                </div>
                                            </td>

                                            <td>
                                                    <span class="badge {{ $validate  ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} rounded-3 py-2 fw-semibold fs-2 d-inline-flex align-items-center gap-1">
                                                    {{ $validate == 1 ? 'Culminado' : 'Pendiente' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
@endsection



@push('scripts')
    <script src="{{ url('managers/libs/owl.carousel/dist/owl.carousel.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('managers/libs/apexcharts/dist/apexcharts.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('distributors/js/enterprises/courses/progress.js') }}"></script>
@endpush
