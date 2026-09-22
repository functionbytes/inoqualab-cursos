@extends('layouts.managers')


@section('page_header')
    @include('enterprises.includes.card', ['title' => 'Editar cliente'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">

            <form id="formUsers" enctype="multipart/form-data" role="form" onSubmit="return false"
                  data-update-url="{{ route('enterprise.users.update') }}"
                  data-redirect-url="{{ route('enterprise.users') }}">

                {{ csrf_field() }}

                <input type="hidden" id="id" name="id" value="{{ $user->id }}">
                <input type="hidden" id="slack" name="slack" value="{{ $user->slack }}">
                <input type="hidden" id="edit" name="edit" value="true">

                <div class="card">

                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">Editar cliente</h6>
                        <p class="text-muted mb-3">
                            Actualiza el celular y la dirección del cliente. El resto de los datos son gestionados por el distribuidor y no se pueden modificar aquí.
                        </p>

                        <div class="row g-3">

                            <div class="col-6">
                                <label class="form-label fw-semibold">Nombres</label>
                                <input type="text" class="form-control" id="firstname"  name="firstname" value="{{ $user->firstname }}" placeholder="Ingresar nombres" disabled>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Apellidos</label>
                                <input type="text" class="form-control" id="lastname"  name="lastname" value="{{ $user->lastname }}" placeholder="Ingresar apellido" disabled>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Identificación</label>
                                <input type="text" class="form-control" id="identification"  name="identification" value="{{ $user->identification }}" placeholder="Ingresar identificación" disabled>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Correo electronico</label>
                                <input type="text" class="form-control" id="email"  name="email" value="{{ $user->email }}" placeholder="Ingresar correo electronico" disabled>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Celular</label>
                                <input type="text" class="form-control" id="cellphone"  name="cellphone" value="{{ $user->cellphone }}" placeholder="Ingresar celular">
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Dirección</label>
                                <input type="text" class="form-control" id="address" name="address" value="{{ $user->address }}" placeholder="Ingresar dirección">
                            </div>

                            <div class="col-12">
                                <div class="errors d-none"></div>
                            </div>

                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100">
                            Guardar
                        </button>
                    </div>

                </div>

            </form>
        </div>

        {{-- Columna derecha: sidebar informativo --}}
        <div class="col-lg-4">

            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Sobre los clientes</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Solo puedes actualizar el celular y la dirección del cliente. Los demás datos se editan desde el portal de distribuidor.</p>
                </div>
            </div>

        </div>

    </div>

@endsection



@push('scripts')
    <script src="{{ asset('enterprises/js/views/users/users/edit.js') }}"></script>
@endpush


