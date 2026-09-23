@extends('layouts.managers')

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

@section('page_header')
    @include('supports.includes.card', [
        'title' => 'Progreso - '.$course->title,
        'description' => 'Detalle del curso y seguimiento de '.$user->firstname.' '.$user->lastname,
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Stats --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Clases</h6>
                                <h4 class="mb-1 fw-bold">{{ $total_count }}</h4>
                                <span class="text-muted">Lecciones del curso</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Porcentaje</h6>
                                <h4 class="mb-1 fw-bold">{{ round($progres) }}%</h4>
                                <span class="text-muted">Avance completado</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Capítulos --}}
            <div class="card-body">
                @php $chapters = $class->groupBy('chapter_id'); @endphp
                @foreach ($chapters->sortBy('position') as $chapter)
                    <div class="mb-4">
                        <h6 class="fw-semibold text-muted mb-3 border-bottom pb-2">{{ $chapter->first()->chapter->title }}</h6>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Lección</th>
                                        <th class="text-center" style="width:160px">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($chapter as $lesson)
                                        @php
                                            $validate = App\Models\Course\CourseProgress::validate($lesson->id, $inscription->id, $user->id) || $inscription->culminated == 1;
                                        @endphp
                                        <tr>
                                            <td class="fw-semibold">{{ $lesson->title }}</td>
                                            <td class="text-center">
                                                <span class="badge {{ $validate ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                                    {{ $validate ? 'Culminado' : 'Pendiente' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>

@endsection
