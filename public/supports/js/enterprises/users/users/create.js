$(document).ready(function () {
    Dropzone.autoDiscover = false;

    const $form = $('#formUsers');
    const checkUrl = $form.data('check-url');
    const storeUrl = $form.data('store-url');
    const redirectUrl = $form.data('redirect-url');

    $('#identification').on('blur', function () {
        const identification = $(this).val();

        $.ajax({
            url: checkUrl,
            method: 'POST',
            data: {
                identification: identification,
                _token: $('meta[name="csrf-token"]').attr('content'),
            },
            success: function (response) {
                if (!response.success) {
                    return;
                }

                if (response.url) {
                    $('.enterprise-div').removeClass('d-none');
                    $('#enterprise-link').attr('href', response.url);
                }

                $('#message-modal .modal-content-message').html(response.message);
                $('#message-modal').modal('show');
            },
            error: function () {
                alert('Error al validar la identificación.');
            },
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
            const formData = new FormData(form);
            const slack = $('#slack').val();
            const firstname = $('#firstname').val();
            const lastname = $('#lastname').val();
            const identification = $('#identification').val();
            const cellphone = $('#cellphone').val();
            const email = $('#email').val();
            const address = $('#address').val();
            const password = $('#password').val();
            const available = $('#available').val();
            const role = $('#roles').val();
            const enterprise = $('#enterprises').val();

            formData.append('slack', slack);
            formData.append('firstname', firstname);
            formData.append('lastname', lastname);
            formData.append('identification', identification);
            formData.append('cellphone', cellphone);
            formData.append('email', email);
            formData.append('address', address);
            formData.append('password', password);
            formData.append('available', available);
            formData.append('role', role);
            formData.append('enterprises', enterprise);

            $.ajax({
                url: storeUrl,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                type: 'POST',
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {
                    if (response.success === true) {
                        toastr.success('Se ha creado correctamente.', 'Operación exitosa', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right',
                        });

                        setTimeout(function () {
                            window.location.href = redirectUrl;
                        }, 4000);
                    } else {
                        $('.errors').removeClass('d-none').text(response.message);
                    }
                },
            });
        },
    });
});
