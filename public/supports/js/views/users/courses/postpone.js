$(document).ready(function () {
    var $range = $('.daterange');

    $range.daterangepicker({
        startDate: $range.data('start'),
        endDate: $range.data('end'),
        locale: {
            format: 'DD/MM/YYYY',
        },
    });

    $('#formAction').validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            range: {
                required: true,
            },
        },
        messages: {
            range: {
                required: 'Es necesario una opción.',
            },
        },
        submitHandler: function (form) {
            var $form = $(form);
            var formData = new FormData($form[0]);

            formData.append('inscription', $('#inscription').val());
            formData.append('range', $('#range').val());

            var $submitButton = $('button[type="submit"]');
            $submitButton.prop('disabled', true);

            $.ajax({
                url: $form.data('action-url'),
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
                            window.location.href = $form.data('redirect-url');
                        }, 1500);
                    } else {
                        $submitButton.prop('disabled', false);

                        toastr.warning(response.message, 'Operación fallida', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right',
                        });
                    }
                },
                error: function () {
                    $submitButton.prop('disabled', false);
                },
            });
        },
    });
});
