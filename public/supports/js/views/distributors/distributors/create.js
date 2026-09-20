$(document).ready(function () {
    var $form = $('#formDistributors');

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
            title: {
                required: true,
                minlength: 3,
                maxlength: 100,
            },
            leading: {
                required: true,
                minlength: 3,
                maxlength: 100,
            },
            supporting: {
                required: true,
                minlength: 3,
                maxlength: 100,
            },
            address: {
                required: true,
                minlength: 3,
                maxlength: 100,
            },
            nit: {
                required: true,
                minlength: 6,
                maxlength: 100,
            },
            cellphone: {
                required: true,
                number: true,
                minlength: 6,
                maxlength: 10,
            },
            email: {
                required: true,
                email: true,
                emailExt: true,
            },
            available: {
                required: true,
            },
        },
        messages: {
            title: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            leading: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            supporting: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            address: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 0 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            nit: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 6 caracter',
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
            available: {
                required: 'Es necesario un estado.',
            },
        },
        submitHandler: function (form) {
            var formData = new FormData($form[0]);

            formData.append('title', $('#title').val());
            formData.append('cellphone', $('#cellphone').val());
            formData.append('email', $('#email').val());
            formData.append('address', $('#address').val());
            formData.append('nit', $('#nit').val());
            formData.append('available', $('#available').val());
            formData.append('supporting', $('#supporting').val());
            formData.append('leading', $('#leading').val());

            var $submitButton = $('button[type="submit"]');
            $submitButton.prop('disabled', true);

            $.ajax({
                url: $form.data('store-url'),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                type: 'POST',
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {
                    if (response.success == true) {
                        toastr.success(response.message, 'Operación exitosa', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right',
                        });

                        setTimeout(function () {
                            window.location.href = $form.data('redirect-url');
                        }, 2000);
                    } else {
                        var error = response.message;
                        $('.errors').removeClass('d-none').html(error);

                        setTimeout(function () {
                            $('.errors').addClass('d-none').html('');
                        }, 2000);
                    }
                },
            });
        },
    });
});
