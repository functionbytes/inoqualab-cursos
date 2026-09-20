Dropzone.autoDiscover = false;

$(document).ready(function () {
    var $form = $('#formRates');

    $form.validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            'courses[]': { required: true, number: true, min: 0 },
        },
        messages: {
            'courses[]': {
                required: 'Es necesario ingresar un precio.',
                number: 'Debe ser un número.',
                min: 'El precio no puede ser negativo.'
            },
        },
        submitHandler: function () {
            var formData = new FormData($form[0]);
            var slack = $('#slack').val();

            $.ajax({
                url: $form.data('update-url'),
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                type: 'POST',
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {
                    var $submitButton = $('button[type="submit"]');

                    if (response.success == true) {
                        toastr.success('Se ha crado un empleado.', 'Operación exitosa', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right'
                        });

                        setTimeout(function () {
                            window.location.href = $form.data('redirect-url').replace(':slack', slack);
                        }, 2000);
                    } else {
                        $submitButton.prop('disabled', false);

                        toastr.warning(response.message, 'Operación fallida', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right'
                        });

                        $('.errors').text(response.message);
                        $('.errors').removeClass('d-none');
                    }
                }
            });
        }
    });
});
