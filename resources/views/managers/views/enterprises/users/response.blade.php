@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Resultado de importación — ' . $enterprise->title])
@endsection
@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">
            <div class="card w-100">
                <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold">Resultado de importación — {{ $enterprise->title }}</h6>
                    <a href="{{ route('manager.enterprises.users', $enterprise->slack) }}" class="btn btn-light btn-sm">
                        Volver a usuarios
                    </a>
                </div>
                <div class="card-body">

                    @if(isset($error_message))
                    <div class="alert alert-danger mb-4">
                        <i class="fas fa-exclamation-circle me-1"></i> {{ $error_message }}
                    </div>
                    @endif

                    @if(isset($failures) && count($failures) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-danger">
                                <tr>
                                    <th>Fila</th>
                                    <th>Atributo</th>
                                    <th>Error</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($failures as $failure)
                                <tr>
                                    <td>{{ $failure->row() }}</td>
                                    <td>{{ $failure->attribute() }}</td>
                                    <td>{{ implode(', ', $failure->errors()) }}</td>
                                    <td>{{ $failure->values()[$failure->attribute()] ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-1"></i> Importación procesada correctamente.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
