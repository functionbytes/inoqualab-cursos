@extends('layouts.managers')

@section('page_header')
    @include('enterprises.includes.card', [
        'title' => 'Resultados - '.$certificate->course->title,
        'description' => 'Examen de '.$certificate->user->firstname.' '.$certificate->user->lastname,
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
                                <h6 class="card-title mb-2">Preguntas correctas</h6>
                                <h4 class="mb-1 fw-bold">{{ $corrects ?? 0 }}</h4>
                                <span class="text-muted">Respuestas acertadas</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Preguntas incorrectas</h6>
                                <h4 class="mb-1 fw-bold">{{ $wrongs ?? 0 }}</h4>
                                <span class="text-muted">Respuestas erradas</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Calificación</h6>
                                <h4 class="mb-1 fw-bold">{{ $exam->score ?? 'N/D' }}</h4>
                                <span class="text-muted">Nota final del examen</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabla --}}
            <div class="card-body">
                @if($answers && $answers->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Pregunta</th>
                                    <th>Respuesta del usuario</th>
                                    <th>Respuesta correcta</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($answers as $answer)
                                    <tr>
                                        <td>{{ Str::words(ucfirst($answer->question->question), 12, '...') }}</td>
                                        <td>{{ $answer->user_answer }}</td>
                                        <td>{{ $answer->answer }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $answer->approved == 1 ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                                {{ $answer->approved == 1 ? 'Correcta' : 'Incorrecta' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-question', 48) !!}</div>
                        <h5 class="fw-bold mb-2">Sin examen registrado</h5>
                        <p class="text-muted mb-0">Este certificado no tiene un examen asociado con respuestas para mostrar.</p>
                    </div>
                @endif
            </div>

        </div>
    </div>

@endsection
