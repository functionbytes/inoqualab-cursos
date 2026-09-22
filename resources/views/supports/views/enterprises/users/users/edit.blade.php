@extends('layouts.managers')


@php ob_start(); @endphp
Editar
                                @if ($user->role == 'manager')
                                    administrador
                                @elseif($user->role == 'customer')
                                    cliente
                                @elseif($user->role == 'enterprises')
                                    empresa
                                @endif
                            @php $__pageTitle = trim(preg_replace('/\s+/', ' ', ob_get_clean())); @endphp

@section('page_header')
    @include('supports.includes.card', ['title' => $__pageTitle])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">

            <form id="formUsers" enctype="multipart/form-data" role="form"
                  data-update-url="{{ route('support.enterprises.users.update') }}"
                  data-redirect-url="{{ route('support.enterprises.users', $enterprise->slack) }}">

                {{ csrf_field() }}

                <input type="hidden" id="id" name="id" value="{{ $user->id }}">
                <input type="hidden" id="slack" name="slack" value="{{ $user->slack }}">
                <input type="hidden" id="enterprise" name="enterprise" value="{{ $enterprise->id }}">
                <input type="hidden" id="edit" name="edit" value="true">

                <div class="card">

                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">{{ $__pageTitle }}</h6>
                        <p class="text-muted mb-3">
                            Actualiza los datos del usuario. Los cambios se guardarán al hacer clic en Guardar.
                        </p>

                        <div class="row g-3">

                            <div class="col-6">
                                <label class="form-label fw-semibold">Nombres</label>
                                <input type="text" class="form-control" id="firstname" name="firstname" value="{{ $user->firstname }}" placeholder="Ingresar nombres" autocomplete="new-password">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Apellidos</label>
                                <input type="text" class="form-control" id="lastname" name="lastname" value="{{ $user->lastname }}" placeholder="Ingresar apellido" autocomplete="new-password">
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Identificación</label>
                                <input type="text" class="form-control" id="identification" name="identification" value="{{ $user->identification }}" placeholder="Ingresar identificación" autocomplete="new-password">
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Correo electronico</label>
                                <input type="text" class="form-control" id="email" name="email" value="{{ $user->email }}" placeholder="Ingresar correo electronico" autocomplete="new-password">
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Dirección</label>
                                <input type="text" class="form-control" id="address" name="address" value="{{ $user->address }}" placeholder="Ingresar dirección" autocomplete="new-password">
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Celular</label>
                                <input type="text" class="form-control" id="cellphone" name="cellphone" value="{{ $user->cellphone }}" placeholder="Ingresar celular" autocomplete="new-password">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" value="" placeholder="Ingresar contraseña" autocomplete="new-password">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Estado</label>
                                <div class="input-group">
                                    {!! Form::select('available', $availables, $user->available , ['class' => 'select2 form-control','id' => 'available']) !!}
                                </div>
                                <label id="available-error" class="error d-none" for="available"></label>
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
                    <h6 class="mb-0 fw-bold">Sobre los usuarios</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">El usuario queda asociado a la empresa actual y tendrá acceso a los cursos e inscripciones que esta le asigne.</p>
                </div>
            </div>

        </div>

    </div>

@endsection



@push('scripts')
    <script src="{{ asset('supports/js/enterprises/users/users/edit.js') }}"></script>
@endpush



