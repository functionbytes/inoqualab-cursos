Dropzone.autoDiscover = false;

$(document).ready(function () {
    var $form = $('#formUsers');

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
            identification: { required: false, minlength: 3, maxlength: 100 },
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
        submitHandler: function () {
            var formData = new FormData($form[0]);
            var slack = $('#slack').val();
            var firstname = $('#firstname').val();
            var lastname = $('#lastname').val();
            var identification = $('#identification').val();
            var cellphone = $('#cellphone').val();
            var email = $('#email').val();
            var address = $('#address').val();
            var password = $('#password').val();
            var enterprise = $('#enterprises').val();

            formData.append('slack', slack);
            formData.append('firstname', firstname);
            formData.append('lastname', lastname);
            formData.append('identification', identification);
            formData.append('cellphone', cellphone);
            formData.append('email', email);
            formData.append('address', address);
            formData.append('password', password);
            formData.append('enterprises', enterprise);

            var $submitButton = $('button[type="submit"]').prop('disabled', true);

            $.ajax({
                url: $form.data('store-url'),
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
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

                        $('#course-modal').modal('show');
                        $('#course-link').attr('href', $form.data('redirect-url'));

                        $submitButton.prop('disabled', false);
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
