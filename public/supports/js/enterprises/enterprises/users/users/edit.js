$(document).ready(function () {
    Dropzone.autoDiscover = false;

    const $form = $('#formUsers');
    const updateUrl = $form.data('update-url');
    const redirectUrl = $form.data('redirect-url');

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
            identification: { required: false, number: true, minlength: 3, maxlength: 100 },
            cellphone: { required: false, number: true, minlength: 6, maxlength: 10 },
            available: { required: true },
            address: { required: false, minlength: 3, maxlength: 100 },
            email: { required: true, email: true, emailExt: true },
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
            password: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 6 caracter',
                maxlength: 'Debe contener al menos 10 caracter',
            },
        },
        submitHandler: function (form) {
            const formData = new FormData(form);
            const slack = $('#slack').val();
            const firstname = $('#firstname').val();
            const lastname = $('#lastname').val();
            const identification = $('#identification').val();
            const cellphone = $('#cellphone').val();
            const email = $('#email').val();
            const password = $('#password').val();
            const enterprise = $('#enterprise').val();
            const address = $('#address').val();
            const available = $('#available').val();

            formData.append('slack', slack);
            formData.append('enterprise', enterprise);
            formData.append('firstname', firstname);
            formData.append('lastname', lastname);
            formData.append('identification', identification);
            formData.append('cellphone', cellphone);
            formData.append('email', email);
            formData.append('address', address);
            formData.append('password', password);
            formData.append('available', available);

            const $submitButton = $form.find('button[type="submit"]');
            $submitButton.prop('disabled', true);

            $.ajax({
                url: updateUrl,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                type: 'POST',
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {
                    if (response.success === true) {
                        toastr.success('Se ha editado correctamente.', 'Operación exitosa', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right',
                        });

                        setTimeout(function () {
                            window.location.href = redirectUrl;
                        }, 4000);
                    } else {
                        $submitButton.prop('disabled', false);

                        $('.errors').removeClass('d-none').html(response.message);

                        setTimeout(function () {
                            $('.errors').addClass('d-none').html();
                        }, 2000);
                    }
                },
            });
        },
    });
});
