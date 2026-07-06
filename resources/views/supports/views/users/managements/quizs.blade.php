@extends('layouts.managers')

@section('content')

    @include('supports.includes.card', ['title' => 'Quizzes - '.$course->title])

    <div class="widget-content searchable-container list">

        <div class="card card-body">
            <div class="d-sm-flex d-block align-items-center justify-content-between">
                <div class="mb-3 mb-sm-0">
                    <h5 class="card-title fw-semibold">Intentos de quiz</h5>
                    <p class="card-subtitle mb-0">{{ $user->firstname }} {{ $user->lastname }} · {{ $course->title }}</p>
                </div>
                @if ($quizs->count() > 0)
                    <a class="btn btn-outline-danger confirm-delete"
                       data-href="{{ route('support.enterprises.users.managements.quiz.restore', $inscription->slack) }}">
                        Reiniciar quizzes
                    </a>
                @endif
            </div>
        </div>

        <div class="card card-body">
            <div class="table-responsive">
                <table class="table align-middle text-nowrap mb-0">
                    <thead class="header-item">
                        <tr>
                            <th>Clase</th>
                            <th class="text-center">Correctas</th>
                            <th class="text-center">Incorrectas</th>
                            <th class="text-center">Puntaje</th>
                            <th class="text-center">Última actualización</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($quizs as $quiz)
                            <tr>
                                <td>
                                    <span class="fw-semibold">{{ $quiz->lesson->title ?? 'Clase' }}</span>
                                </td>
                                <td class="text-center">{{ $quiz->correct }}</td>
                                <td class="text-center">{{ $quiz->wrong }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $quiz->score >= 80 ? 'bg-light-success text-success' : 'bg-light-warning text-warning' }} rounded-3 py-2 fw-semibold fs-2">
                                        {{ $quiz->score }}%
                                    </span>
                                </td>
                                <td class="text-center">{{ optional($quiz->updated_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    El estudiante aún no ha presentado ningún quiz en este curso.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
