"use strict";

$(function () {
    var config = $('#profile-edit').data('config') || {};

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
            url: config.routes.profileUpdate,
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
            url: config.routes.passwordUpdate,
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
});
