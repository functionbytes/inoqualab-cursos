@extends('layouts.managers')

@section('title', 'Mi perfil')

@section('content')
<div class="container-fluid">

    <div class="row">
        <div class="col-12">
            <h4 class="fw-semibold mb-4">Mi perfil</h4>
        </div>
    </div>

    <div class="row">
        {{-- Información básica --}}
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title fw-semibold mb-4">Información básica</h5>

                    <form id="profileForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="_method" value="PUT">

                        <div class="d-flex align-items-center mb-4">
                            <img id="avatarPreview"
                                 src="{{ $user->image ? url($user->image) : url('managers/images/profile/profile.jpg') }}"
                                 class="rounded-circle object-fit-cover me-3" width="80" height="80"
                                 alt="Foto de perfil"
                                 onerror="this.src='{{ url('managers/images/profile/profile.jpg') }}'">
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

                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary" id="profileSubmit">Guardar cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Seguridad --}}
        <div class="col-lg-5">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title fw-semibold mb-4">Seguridad</h5>

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

                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary" id="passwordSubmit">Actualizar contraseña</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    "use strict";

    // Limpia el estado de validación de un formulario.
    function clearErrors($form) {
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback[data-error]').text('');
    }

    // Pinta los errores 422 por campo.
    function showErrors($form, errors) {
        $.each(errors, function (field, messages) {
            var key = field.split('.')[0];
            var $input = $form.find('[name="' + key + '"]');
            $input.addClass('is-invalid');
            $form.find('.invalid-feedback[data-error="' + key + '"]').text(messages[0]);
        });
    }

    var toastrOpts = { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' };

    // ── Datos del perfil ──────────────────────────────────────────────
    $('#profileForm').on('submit', function (e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $('#profileSubmit');
        clearErrors($form);
        $btn.prop('disabled', true);

        $.ajax({
            url: '{{ route('manager.profile.update') }}',
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function (res) {
                toastr.success(res.message, 'Listo', toastrOpts);
                if (res.image) {
                    $('#avatarPreview').attr('src', res.image + '?t=' + Date.now());
                    $('.user-profile-img img, .profile-dropdown img').attr('src', res.image + '?t=' + Date.now());
                }
            },
            error: function (xhr) {
                if (xhr.status === 422 && xhr.responseJSON) {
                    showErrors($form, xhr.responseJSON.errors || {});
                    toastr.warning('Revisa los campos marcados.', 'Advertencia', toastrOpts);
                } else {
                    toastr.error('No se pudo actualizar el perfil.', 'Error', toastrOpts);
                }
            },
            complete: function () { $btn.prop('disabled', false); }
        });
    });

    // ── Cambio de contraseña ──────────────────────────────────────────
    $('#passwordForm').on('submit', function (e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $('#passwordSubmit');
        clearErrors($form);
        $btn.prop('disabled', true);

        $.ajax({
            url: '{{ route('manager.profile.password') }}',
            type: 'POST',
            data: $form.serialize(),
            success: function (res) {
                toastr.success(res.message, 'Listo', toastrOpts);
                $form[0].reset();
            },
            error: function (xhr) {
                if (xhr.status === 422 && xhr.responseJSON) {
                    showErrors($form, xhr.responseJSON.errors || {});
                    toastr.warning('Revisa los campos marcados.', 'Advertencia', toastrOpts);
                } else {
                    toastr.error('No se pudo actualizar la contraseña.', 'Error', toastrOpts);
                }
            },
            complete: function () { $btn.prop('disabled', false); }
        });
    });
</script>
@endpush
