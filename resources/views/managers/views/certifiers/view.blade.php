@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <div class="card-body border-top">
                    <div class="d-flex no-block align-items-center">
                        <h5 class="mb-0">Detalle del capacitador</h5>
                    </div>
                    <p class="card-subtitle mb-3 mt-3">
                        Información del capacitador registrado en el sistema.
                    </p>

                    <div class="row">

                        <div class="col-6">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Nombres</label>
                                <input type="text" class="form-control" value="{{ $certifier->firstname }}" disabled>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Apellidos</label>
                                <input type="text" class="form-control" value="{{ $certifier->lastname }}" disabled>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Identificación</label>
                                <input type="text" class="form-control" value="{{ $certifier->identification }}" disabled>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Profesión</label>
                                <input type="text" class="form-control" value="{{ $certifier->profession }}" disabled>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Estado</label>
                                <input type="text" class="form-control" value="{{ $certifier->available ? 'Activo' : 'Inactivo' }}" disabled>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Fecha de creación</label>
                                <input type="text" class="form-control" value="{{ $certifier->created_at ? date('Y-m-d', strtotime($certifier->created_at)) : '-' }}" disabled>
                            </div>
                        </div>

                        @if($certifier->description)
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Descripción</label>
                                <div class="border rounded p-3 bg-light">
                                    {!! $certifier->description !!}
                                </div>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>

            </div>

        </div>
    </div>

@endsection
