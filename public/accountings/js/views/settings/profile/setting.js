$(document).on('submit', '#formUsers', function (e) {
    e.preventDefault();
});

$(document).ready(function () {
    const $form = $('#formUsers');
    const updateUrl = $form.data('update-url');
    const dashboardUrl = $form.data('dashboard-url');

    jQuery.validator.addMethod(
        'emailExt',
        function (value) {
            return value.match(
                /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i
            );
        },
        'Porfavor ingrese email valido'
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
            cellphone: {
                required: false,
                number: true,
                minlength: 6,
                maxlength: 10,
            },
            email: {
                required: true,
                email: true,
                emailExt: true,
            },
            password: {
                required: false,
                minlength: 3,
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
            email: {
                required: 'Tu email ingresar correo electrónico es necesario.',
                email: 'Por favor, introduce una dirección de correo electrónico válida.',
            },
            password: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 6 caracter',
                maxlength: 'Debe contener al menos 10 caracter',
            },
        },
        submitHandler: function (form) {
            const formData = new FormData(form);
            formData.append('slack', $('#slack').val());
            formData.append('firstname', $('#firstname').val());
            formData.append('lastname', $('#lastname').val());
            formData.append('cellphone', $('#cellphone').val());
            formData.append('email', $('#email').val());
            formData.append('password', $('#password').val());

            const $submitButton = $('button[type="submit"]');
            $submitButton.prop('disabled', true);

            $.ajax({
                url: updateUrl,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                type: 'POST',
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {
                    $submitButton.prop('disabled', false);

                    if (response.success == true) {
                        toastr.success('Se ha editado correctamente el perfil.', 'Operación exitosa', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right',
                        });

                        setTimeout(function () {
                            window.location.href = dashboardUrl;
                        }, 2000);
                    } else {
                        toastr.warning('Se ha generado un error.', 'Operación fallida', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right',
                        });

                        $('.errors').removeClass('d-none');
                        $('.errors').html(response.message);
                    }
                },
                error: function (xhr) {
                    $submitButton.prop('disabled', false);

                    const messages = [];

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

                    $('.errors').removeClass('d-none');
                    $('.errors').html(messages.join('<br>'));
                },
            });
        },
    });
});
