$(document).ready(function () {

    jQuery.validator.addMethod('emailExt', function (value, element, param) {
        return value.match(/^[a-zA-Z0-9_\.%\+\-]+@[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,3}$/);
    }, 'Porfavor ingrese email valido');

    jQuery.validator.addMethod(
        'validationTxt',
        function (value, element, param) {
            return value.match(
                /^[ a-zA-ZñÑáéíóúÁÉÍÓÚ]+$/,
            )
        },
        'Por favor ingrese solo letras',
    )

    $('#terms').on('change', function () {
        value = $(this).is(':checked');
        if (value == true) {
            $('#submitContacts').removeClass('contact-disabled');
        } else {
            $('#submitContacts').addClass('contact-disabled');
        }
    });

    $('#submitContacts').click(function () {
        if ($(this).hasClass('contact-disabled')) return false;
        $('#formContacts').submit();
    });

    $('#formContacts').validate({
        submit: false,
        ignore: '.ignore',
        errorClass: 'error show-error',
        validClass: 'valid',
        rules: {
            firstname: {
                validationTxt: true,
                required: true,
                minlength: 3,
                maxlength: 30
            },
            lastname: {
                validationTxt: true,
                required: true,
                minlength: 3,
                maxlength: 30
            },
            email: {
                required: true,
                email: true,
                emailExt: true
            },
            cellphone: {
                required: true,
                number: true,
                minlength: 8,
                maxlength: 500
            },
            message: {
                required: true,
                minlength: 3,
                maxlength: 8000
            }
        },
        messages: {
            firstname: {
                text: 'Este campo es obligatorio.',
                required: 'El campo nombre es necesario.',
                minlength: 'El nombre debe contener al menos 3 caracteres.',
                maxlength: 'El nombre  debe contener no mas de 30 caracteres'
            },
            lastname: {
                text: 'Este campo es obligatorio.',
                required: 'El campo nombre es necesario.',
                minlength: 'El nombre debe contener al menos 3 caracteres.',
                maxlength: 'El nombre  debe contener no mas de 30 caracteres'
            },
            email: {
                required: 'El email es necesario',
                email: 'Por favor ingrese email valido'
            },
            cellphone: {
                required: 'La celular es necesario',
                minlength: 'La celular debe contener al menos 6 caracteres',
                maxlength: 'La celular debe contener no mas de 20 caracteres',
                number: 'Sólo se pueden ingresar números'
            },
            message: {
                required: 'Este campo es obligatorio.',
                minlength: 'El mensaje debe contener al menos 3 caracteres.',
                maxlength: 'El mensaje  debe contener no mas de 8000 caracteres',
            }
        },
        errorPlacement: function (error, element) {
            $('#' + element.attr('id') + '-error')
                .removeClass('d-none')
                .addClass('show-error')
                .html(error.html());
        },
        submitHandler: function (form) {

            var $form = $('#formContacts');
            var formData = new FormData($form[0]);
            var firstname = $('#firstname').val();
            var lastname = $('#lastname').val();
            var email = $('#email').val();
            var cellphone = $('#cellphone').val();
            var message = $('#message').val();

            formData.append('firstname', firstname);
            formData.append('lastname', lastname);
            formData.append('email', email);
            formData.append('cellphone', cellphone);
            formData.append('message', message);

            var $submitBtn = $('#submitContacts');
            $submitBtn.addClass('contact-disabled');

            $.ajax({
                url: $form.data('store-url'),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {

                    if (response.success == true) {

                        toastr.success('¡Mensaje enviado correctamente! Nos pondremos en contacto pronto.', 'Enviado', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right',
                            timeOut: 5000
                        });

                        setTimeout(function () {
                            $('#firstname').val('');
                            $('#lastname').val('');
                            $('#email').val('');
                            $('#cellphone').val('');
                            $('#message').val('');
                            $('#terms').prop('checked', false);
                            $submitBtn.addClass('contact-disabled');
                        }, 500);

                    } else {

                        if ($('#terms').is(':checked')) $submitBtn.removeClass('contact-disabled');

                        toastr.warning(response.message || 'Hubo un problema al enviar el mensaje. Inténtalo de nuevo.', 'Error', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right'
                        });

                        $('.errors').text(response.message || '').removeClass('d-none');

                    }

                },
                error: function () {
                    if ($('#terms').is(':checked')) $submitBtn.removeClass('contact-disabled');
                    toastr.error('Error al enviar el mensaje. Por favor, inténtalo más tarde.', 'Error', {
                        closeButton: true,
                        progressBar: true,
                        positionClass: 'toast-bottom-right'
                    });
                }
            });

        }

    });
});
