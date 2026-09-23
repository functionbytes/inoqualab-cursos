@extends('layouts.managers')


@section('page_header')
    @include('accountings.includes.card', ['title' => 'Editar usuario'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">

            <form id="formUsers" enctype="multipart/form-data" role="form"
                  data-update-url="{{ route('accounting.profile.update') }}"
                  data-dashboard-url="{{ route('accounting.dashboard', '') }}">

                {{ csrf_field() }}

                <input type="hidden" id="id" name="id" value="{{ $user->id }}">
                <input type="hidden" id="slack" name="slack" value="{{ $user->slack }}">
                <input type="hidden" id="edit" name="edit" value="true">

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Editar usuario</h6>
                        <p class="text-muted small mb-0">
                            Actualiza tus datos personales de acceso al panel de contabilidad: nombre,
                            correo y contraseña. Deja la contraseña en blanco si no quieres cambiarla.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-6">
                                <label class="form-label fw-semibold">Nombres</label>
                                <input type="text" class="form-control" id="firstname" name="firstname" value="{{ $user->firstname }}" placeholder="Ingresar nombres">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Apellidos</label>
                                <input type="text" class="form-control" id="lastname" name="lastname" value="{{ $user->lastname }}" placeholder="Ingresar apellido">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Correo electronico</label>
                                <input type="text" class="form-control" id="email" name="email" value="{{ $user->email }}" placeholder="Ingresar correo electronico">
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Celular</label>
                                <input type="text" class="form-control" id="cellphone" name="cellphone" value="{{ $user->cellphone }}" placeholder="Ingresar celular">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" value="" placeholder="Ingresar contraseña">
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
                    <h6 class="mb-0 fw-bold">Sobre este perfil</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Estos son tus propios datos de acceso al panel de contabilidad. Deja la contraseña en blanco si no quieres cambiarla.</p>
                </div>
            </div>

        </div>

    </div>

@endsection



@push('scripts')
    <script src="{{ asset('accountings/js/views/settings/profile/setting.js') }}"></script>
@endpush



