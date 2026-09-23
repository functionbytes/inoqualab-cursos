@extends('layouts.managers')

@php
    // La inscripcion es la fuente de verdad de si el curso esta
    // culminado. Algunas inscripciones antiguas solo tienen un
    // registro "resumen" en course_progress (lesson_id null) sin
    // el detalle por leccion: contarlo con count($progress) infla
    // el % (1 de 16 = 6%) mientras la tabla de abajo (que exige
    // lesson_id valido) mostraba todo "Pendiente" -- contradictorio
    // con un curso ya culminado.
    $totalClass = count($class);
    if ($inscription->culminated == 1) {
        $progres = 100;
    } elseif ($totalClass == 0) {
        $progres = 0;
    } else {
        $progres = ($completedLessons->count() / $totalClass) * 100;
    }
@endphp

@section('page_header')
    @include('enterprises.includes.card', [
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
                                <h4 class="mb-1 fw-bold">{{ $totalClass }}</h4>
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
                                        @php $validate = $completedLessons->has($lesson->id) || $inscription->culminated == 1; @endphp
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
