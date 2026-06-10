@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">
            <div class="card w-100">
                <div class="card-body border-top">
                    <div class="d-flex no-block align-items-center mb-3">
                        <h5 class="mb-0">Resultado de importación de cursos</h5>
                        <div class="ms-auto">
                            <a href="{{ route('manager.enterprises.courses', $enterprise->slack) }}" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </a>
                        </div>
                    </div>

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
                                    <th>Valor ingresado</th>
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
                        <i class="fas fa-check-circle me-1"></i> Importación completada sin errores.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
