$(document).ready(function () {
    var $form = $('#formDistributors');

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
            leading: { required: true, minlength: 3, maxlength: 100 },
            supporting: { required: true, minlength: 3, maxlength: 100 },
            address: { required: true, minlength: 3, maxlength: 100 },
            nit: { required: true, minlength: 6, maxlength: 100 },
            cellphone: { required: false, number: true, minlength: 6, maxlength: 10 },
            email: { required: true, email: true, emailExt: true },
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
        },
        submitHandler: function () {
            var formData = new FormData($form[0]);
            var title = $('#title').val();
            var cellphone = $('#cellphone').val();
            var email = $('#email').val();
            var address = $('#address').val();
            var nit = $('#nit').val();
            var supporting = $('#supporting').val();
            var leading = $('#leading').val();

            formData.append('title', title);
            formData.append('cellphone', cellphone);
            formData.append('email', email);
            formData.append('address', address);
            formData.append('nit', nit);
            formData.append('supporting', supporting);
            formData.append('leading', leading);

            var $submitButton = $('button[type="submit"]').prop('disabled', true);

            $.ajax({
                url: $form.data('update-url'),
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
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
                            window.location.href = $form.data('redirect-url');
                        }, 2000);
                    } else {
                        $submitButton.prop('disabled', false);

                        var error = response.message;
                        $('.errors').removeClass('d-none');
                        $('.errors').html(error);

                        setTimeout(function () {
                            $('.errors').addClass('d-none');
                            $('.errors').html('');
                        }, 2000);
                    }
                }
            });
        }
    });
});
