Dropzone.autoDiscover = false;

$(document).ready(function () {
    var $form = $('#formInscriptions');

    $form.validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            course: {
                required: true,
            },
            'user[]': {
                required: true,
            },
        },
        messages: {
            course: {
                required: 'Es necesario una opción.',
            },
            'user[]': {
                required: 'Es necesario una opción.',
            },
        },
        submitHandler: function (form) {
            var formData = new FormData($form[0]);

            formData.append('enterprises', $('#enterprise').val());
            formData.append('users', $('#users').val());
            formData.append('course', $('#course').val());

            var $submitButton = $('button[type="submit"]');
            $submitButton.prop('disabled', true);

            $.ajax({
                url: '/manager/enterprises/inscriptions/generate',
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
                            var slack = $('#enterprise').val();
                            window.location.href = $form.data('redirect-url-template').replace(':slack', slack);
                        }, 2000);
                    } else {
                        $submitButton.prop('disabled', false);

                        var error = response.message;
                        toastr.warning(error, 'Operación fallida', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right',
                        });

                        $('.errors').text(error).removeClass('d-none');
                    }
                },
            });
        },
    });
});
