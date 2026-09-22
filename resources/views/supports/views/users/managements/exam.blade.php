@extends('layouts.managers')

@section('page_header')
    @include('supports.includes.card', ['title' => 'Examen final - '.$course->title])
@endsection

@section('content')

    <div class="widget-content searchable-container list">

        <div class="card card-body">
            <div class="d-sm-flex d-block align-items-center justify-content-between">
                <div class="mb-3 mb-sm-0">
                    <h5 class="card-title fw-semibold">Examen final</h5>
                    <p class="card-subtitle mb-0">{{ $user->firstname }} {{ $user->lastname }} · {{ $course->title }}</p>
                </div>
                @if ($exam)
                    <a class="btn btn-outline-danger confirm-delete"
                       data-href="{{ route('support.enterprises.users.managements.exam.restore', $inscription->slack) }}">
                        Reiniciar examen
                    </a>
                @endif
            </div>
        </div>

        <div class="card card-body">
            @if ($exam)
                <div class="table-responsive">
                    <table class="table align-middle text-nowrap mb-0">
                        <thead class="header-item">
                            <tr>
                                <th class="text-center">Correctas</th>
                                <th class="text-center">Incorrectas</th>
                                <th class="text-center">Puntaje</th>
                                <th class="text-center">Última actualización</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center">{{ $exam->correct }}</td>
                                <td class="text-center">{{ $exam->wrong }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $exam->score >= 80 ? 'bg-light-success text-success' : 'bg-light-warning text-warning' }} rounded-3 py-2 fw-semibold fs-2">
                                        {{ $exam->score }}%
                                    </span>
                                </td>
                                <td class="text-center">{{ optional($exam->updated_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center text-muted py-5">
                    El estudiante aún no ha presentado el examen final de este curso.
                </div>
            @endif
        </div>
    </div>

@endsection
