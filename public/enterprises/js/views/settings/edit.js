$(document).ready(function () {
    jQuery.validator.addMethod(
        'emailExt',
        function (value, element, param) {
            return value.match(
                /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i,
            );
        },
        'Porfavor ingrese email valido',
    );

    $('#formUsers').validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            firstname: {
                required: true,
                minlength: 3,
                maxlength: 100,
            },
            lastname: {
                required: true,
                minlength: 3,
                maxlength: 100,
            },
            identification: {
                required: false,
                minlength: 3,
                maxlength: 100,
            },
            address: {
                required: false,
                minlength: 3,
                maxlength: 100,
            },
            password: {
                required: false,
                minlength: 6,
                maxlength: 100,
            },
        },
        messages: {
            firstname: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            lastname: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            identification: {
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            address: {
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            password: {
                minlength: 'Debe contener al menos 6 caracteres',
                maxlength: 'No puede superar los 100 caracteres',
            },
        },
        submitHandler: function (form) {
            var $form = $('#formUsers');
            var formData = new FormData($form[0]);
            var $submitButton = $('button[type="submit"]');
            $submitButton.prop('disabled', true);

            $.ajax({
                url: $form.data('update-url'),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                type: 'POST',
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {
                    $submitButton.prop('disabled', false);

                    if (response.success) {
                        toastr.success('Perfil actualizado correctamente');
                        $('#password').val('');
                        $('.errors').addClass('d-none').html('');
                    } else {
                        toastr.warning(response.message || 'Se ha generado un error.');
                        $('.errors').removeClass('d-none').html(response.message || '');
                    }
                },
                error: function (xhr) {
                    $submitButton.prop('disabled', false);

                    var messages = [];

                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        $.each(xhr.responseJSON.errors, function (field, errs) {
                            messages.push(errs[0]);
                        });
                    } else {
                        messages.push('Se ha generado un error inesperado.');
                    }

                    toastr.warning('Revisa los datos del formulario.');
                    $('.errors').removeClass('d-none').html(messages.join('<br>'));
                },
            });
        },
    });
});
