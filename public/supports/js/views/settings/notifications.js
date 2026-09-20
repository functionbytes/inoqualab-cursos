$(document).ready(function () {
    Dropzone.autoDiscover = false;

    var $form = $('#formNotifications');
    var updateUrl = $form.data('update-url');
    var redirectUrl = $form.data('redirect-url');

    $form.validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            mail_notification: {
                required: false,
            },
            inscription_notification: {
                required: false,
            },
            invoice_notification: {
                required: false,
            },
        },
        message: {
            mail_notification: {
                required: 'El parametro es necesario.',
            },
            inscription_notification: {
                required: 'El parametro es necesario.',
            },
            invoice_notification: {
                required: 'El parametro es necesario.',
            },
        },
        submitHandler: function (form) {
            var formData = new FormData(form);
            formData.append('slack', $('#slack').val());
            formData.append('mail_notification', $('#mail_notification').is(':checked'));
            formData.append('inscription_notification', $('#inscription_notification').is(':checked'));
            formData.append('invoice_notification', $('#invoice_notification').is(':checked'));

            var $submitButton = $('button[type="submit"]');
            $submitButton.prop('disabled', true);

            $.ajax({
                url: updateUrl,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                type: 'POST',
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {
                    if (response.success == true) {
                        toastr.success('Se ha editado correctamente el perfil.', 'Operación exitosa', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right',
                        });

                        setTimeout(function () {
                            window.location.href = redirectUrl;
                        }, 2000);
                    } else {
                        $submitButton.prop('disabled', false);

                        toastr.warning('Se ha generado un error.', 'Operación fallida', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right',
                        });

                        $('.errors').removeClass('d-none').html(response.message);
                    }
                },
                error: function (xhr) {
                    $submitButton.prop('disabled', false);

                    toastr.warning('Se ha generado un error.', 'Operación fallida', {
                        closeButton: true,
                        progressBar: true,
                        positionClass: 'toast-bottom-right',
                    });
                },
            });
        },
    });
});
