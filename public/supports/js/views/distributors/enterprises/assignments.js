Dropzone.autoDiscover = false;

$(document).ready(function () {
    var $form = $('#formEnterprises');

    $form.validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            'enterprises[]': {
                required: true,
            },
        },
        messages: {
            'enterprises[]': {
                required: 'Es necesario una opción.',
            },
        },
        submitHandler: function (form) {
            var formData = new FormData($form[0]);

            formData.append('slack', $('#slack').val());
            formData.append('enterprises', $('#enterprises').val());

            var $submitButton = $('button[type="submit"]');
            $submitButton.prop('disabled', true);

            $.ajax({
                url: $form.data('update-url'),
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
                            var slack = $('#slack').val();
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
