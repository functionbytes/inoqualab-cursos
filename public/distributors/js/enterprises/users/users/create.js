$(document).ready(function () {
    Dropzone.autoDiscover = false;

    var $form = $('#formUsers');

    $('#identification').on('blur', function () {
        var identification = $(this).val();

        $.ajax({
            url: $form.data('checkUrl'),
            method: 'POST',
            data: {
                identification: identification,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    if (response.url) {
                        $('.enterprise-div').removeClass('d-none');
                        $('#enterprise-link').attr('href', response.url);
                    }
                    $('#message-modal .modal-content-message').html(response.message);
                    $('#message-modal').modal('show');
                }
            },
            error: function () {
                alert('Error al validar la identificación.');
            }
        });
    });

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
            firstname: { required: true, minlength: 3, maxlength: 100 },
            lastname: { required: true, minlength: 3, maxlength: 100 },
            identification: { required: true, number: true, minlength: 3, maxlength: 100 },
            cellphone: { required: false, number: true, minlength: 6, maxlength: 10 },
            email: { required: true, email: true, emailExt: true },
            address: { required: false, minlength: 3, maxlength: 100 },
            password: { required: false, minlength: 3, maxlength: 100 },
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
                number: 'Solo se puede ingresar números.',
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
            var formData = new FormData(form);

            formData.append('slack', $('#slack').val());
            formData.append('firstname', $('#firstname').val());
            formData.append('lastname', $('#lastname').val());
            formData.append('identification', $('#identification').val());
            formData.append('cellphone', $('#cellphone').val());
            formData.append('email', $('#email').val());
            formData.append('address', $('#address').val());
            formData.append('password', $('#password').val());
            formData.append('role', $('#roles').val());
            formData.append('enterprises', $('#enterprises').val());

            $.ajax({
                url: $form.data('storeUrl'),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {
                    if (response.success == true) {
                        $('#firstname').val('');
                        $('#lastname').val('');
                        $('#identification').val('');
                        $('#cellphone').val('');
                        $('#email').val('');
                        $('#address').val('');
                        $('#password').val('');

                        $('#users-modal').modal('show');
                        $('#users-modal .enterprise-div').removeClass('d-none');
                        $('#enterprise-link').attr('href', $form.data('redirectUrl'));
                    } else {
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
