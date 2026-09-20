$(document).ready(function () {
    var $form = $('#formEnterprises');

    jQuery.validator.addMethod(
        'emailExt',
        function (value) {
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
            title: { required: true, minlength: 3, maxlength: 100 },
            address: { required: true, minlength: 3, maxlength: 100 },
            nit: { required: true, minlength: 6, maxlength: 100 },
            cellphone: { required: false, number: true, minlength: 6, maxlength: 10 },
            email: { required: true, email: true, emailExt: true },
            available: { required: true },
        },
        messages: {
            title: {
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
            var formData = new FormData(form);
            var $submitButton = $('button[type="submit"]');
            $submitButton.prop('disabled', true);

            $.ajax({
                url: $form.data('updateUrl'),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
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
                            positionClass: 'toast-bottom-right'
                        });

                        setTimeout(function () {
                            window.location.href = $form.data('redirectUrl');
                        }, 4000);
                    } else {
                        $submitButton.prop('disabled', false);

                        $('.errors').removeClass('d-none');
                        $('.errors').html(response.message);

                        setTimeout(function () {
                            $('.errors').addClass('d-none');
                            $('.errors').html();
                        }, 2000);
                    }
                }
            });
        }
    });
});
