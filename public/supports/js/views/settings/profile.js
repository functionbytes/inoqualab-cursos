$(document).ready(function () {
    Dropzone.autoDiscover = false;

    var $form = $('#formUsers');
    var updateUrl = $form.data('update-url');
    var redirectUrl = $form.data('redirect-url');

    jQuery.validator.addMethod(
        'emailExt',
        function (value, element, param) {
            return value.match(
                /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i,
            );
        },
        'Porfavor ingrese email valido',
    );

    $form.validate({
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
            support: {
                required: false,
                minlength: 3,
                maxlength: 100,
            },
            email: {
                required: true,
                email: true,
                emailExt: true,
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
            support: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            email: {
                required: 'Tu email ingresar correo electrónico es necesario.',
                email: 'Por favor, introduce una dirección de correo electrónico válida.',
            },
            password: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 6 caracteres',
                maxlength: 'No puede superar los 100 caracteres',
            },
        },
        submitHandler: function (form) {
            var formData = new FormData(form);
            formData.append('slack', $('#slack').val());
            formData.append('firstname', $('#firstname').val());
            formData.append('lastname', $('#lastname').val());
            formData.append('support', $('#support').val());
            formData.append('email', $('#email').val());
            formData.append('password', $('#password').val());

            var $submitButton = $('button[type="submit"]');
            $submitButton.prop('disabled', true);

            $.ajax({
                url: updateUrl,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                type: 'POST',
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {
                    if (response.success == true) {
                        toastr.success('Se ha editado correctamente el perfil.', 'Operación exitosa', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right',
                        });

                        setTimeout(function () {
                            window.location.href = redirectUrl;
                        }, 2000);
                    } else {
                        $submitButton.prop('disabled', false);

                        toastr.warning('Se ha generado un error.', 'Operación fallida', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right',
                        });

                        $('.errors').removeClass('d-none').html(response.message);
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

                    toastr.warning('Revisa los datos del formulario.', 'Operación fallida', {
                        closeButton: true,
                        progressBar: true,
                        positionClass: 'toast-bottom-right',
                    });

                    $('.errors').removeClass('d-none').html(messages.join('<br>'));
                },
            });
        },
    });
});
