Dropzone.autoDiscover = false;

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

    var $form = $('#formUsers');

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
            identification: {
                required: false,
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
            address: {
                required: false,
                minlength: 3,
                maxlength: 100,
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
            identification: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            cellphone: {
                required: 'El parametro es necesario.',
                number: 'Solo se puede ingresar números.',
                minlength: 'Debe contener al menos 6 caracter',
                maxlength: 'Debe contener al menos 10 caracter',
            },
            email: {
                required: 'Tu email ingresar correo electrónico es necesario.',
                email: 'Por favor, introduce una dirección de correo electrónico válida.',
            },
            address: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            password: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 6 caracter',
                maxlength: 'Debe contener al menos 10 caracter',
            },
        },
        submitHandler: function (form) {
            var formData = new FormData($form[0]);

            formData.append('slack', $('#slack').val());
            formData.append('firstname', $('#firstname').val());
            formData.append('lastname', $('#lastname').val());
            formData.append('identification', $('#identification').val());
            formData.append('cellphone', $('#cellphone').val());
            formData.append('email', $('#email').val());
            formData.append('address', $('#address').val());
            formData.append('password', $('#password').val());

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
                    if (response.success == true) {
                        toastr.success('Se ha editado correctamente.', 'Operación exitosa', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right',
                        });

                        setTimeout(function () {
                            window.location.href = $form.data('redirect-url');
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
            });
        },
    });
});
