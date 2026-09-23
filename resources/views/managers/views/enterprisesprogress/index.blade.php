@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Progreso — ' . $user->firstname . ' ' . $user->lastname])
@endsection
@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">
            <div class="card w-100">
                <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold">Progreso — {{ $user->firstname }} {{ $user->lastname }}</h6>
                    <a href="{{ route('manager.enterprises.courses.view', [$user->relations?->slack ?? '', $course->slack]) }}" class="btn btn-light btn-sm">
                        Volver
                    </a>
                </div>
                <div class="card-body">

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <p class="text-muted mb-1 small">Curso</p>
                                    <h6 class="mb-0">{{ $course->title }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <p class="text-muted mb-1 small">Progreso general</p>
                                    <h6 class="mb-0">{{ $inscription->percent ?? 0 }}%</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <p class="text-muted mb-1 small">Estado</p>
                                    <h6 class="mb-0">{{ $inscription->culminated ? 'Culminado' : 'En progreso' }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6 class="mb-3">Lecciones</h6>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Lección</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($class as $lesson)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $lesson->title }}</td>
                                    <td>
                                        @php
                                            $done = $progress->where('lesson_id', $lesson->id)->isNotEmpty();
                                        @endphp
                                        @if($done)
                                            <span class="badge bg-success">Completado</span>
                                        @else
                                            <span class="badge bg-secondary">Pendiente</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Sin lecciones registradas.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
