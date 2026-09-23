@extends('layouts.managers')

@section('title', 'Mi perfil')

@section('page_header')
    @include('managers.includes.card', ['title' => 'Mi perfil'])
@endsection

@section('content')
<div class="container-fluid" id="profile-edit"
     data-config='@php $__jsonInline1 = [
        "routes" => [
            "profileUpdate" => route("manager.profile.update"),
            "passwordUpdate" => route("manager.profile.password"),
        ],
     ]; @endphp@json($__jsonInline1)'>

    <div class="row">
        {{-- Información básica --}}
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Información básica</h6>
                </div>
                <div class="card-body">
                    <form id="profileForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="_method" value="PUT">

                        <div class="d-flex align-items-center mb-4">
                            <img id="avatarPreview"
                                 src="{{ $user->image ? url($user->image) : url('managers/images/profile/profile.jpg') }}"
                                 class="rounded-circle object-fit-cover me-3" width="80" height="80"
                                 alt="Foto de perfil"
                                 data-fallback-src="{{ url('managers/images/profile/profile.jpg') }}">
                            <div>
                                <label for="avatar" class="form-label mb-1">Foto de perfil</label>
                                <input type="file" class="form-control" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp">
                                <div class="form-text">JPG, PNG o WEBP. Máximo 2 MB.</div>
                                <div class="invalid-feedback" data-error="avatar"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstname" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="firstname" name="firstname" value="{{ $user->firstname }}">
                                <div class="invalid-feedback" data-error="firstname"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lastname" class="form-label">Apellidos</label>
                                <input type="text" class="form-control" id="lastname" name="lastname" value="{{ $user->lastname }}">
                                <div class="invalid-feedback" data-error="lastname"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Correo electrónico</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}">
                                <div class="invalid-feedback" data-error="email"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cellphone" class="form-label">Celular</label>
                                <input type="text" class="form-control" id="cellphone" name="cellphone" value="{{ $user->cellphone }}">
                                <div class="invalid-feedback" data-error="cellphone"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="identification" class="form-label">Identificación</label>
                                <input type="text" class="form-control" id="identification" name="identification" value="{{ $user->identification }}">
                                <div class="invalid-feedback" data-error="identification"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="address" class="form-label">Dirección</label>
                                <input type="text" class="form-control" id="address" name="address" value="{{ $user->address }}">
                                <div class="invalid-feedback" data-error="address"></div>
                            </div>
                        </div>

                    </form>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary w-100" id="profileSubmit" form="profileForm">Guardar cambios</button>
                </div>
            </div>
        </div>

        {{-- Seguridad --}}
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Seguridad</h6>
                </div>
                <div class="card-body">
                    <form id="passwordForm">
                        @csrf
                        <input type="hidden" name="_method" value="PUT">

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Contraseña actual</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" autocomplete="current-password">
                            <div class="invalid-feedback" data-error="current_password"></div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Nueva contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" autocomplete="new-password">
                            <div class="form-text">Mínimo 8 caracteres.</div>
                            <div class="invalid-feedback" data-error="password"></div>
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirmar nueva contraseña</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" autocomplete="new-password">
                        </div>

                    </form>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary w-100" id="passwordSubmit" form="passwordForm">Actualizar contraseña</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/profile/edit.js') }}"></script>
@endpush
