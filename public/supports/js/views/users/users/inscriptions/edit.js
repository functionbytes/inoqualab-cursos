Dropzone.autoDiscover = false;

$(document).ready(function () {
    var $form = $('#formAction');
    var $range = $('.daterange');

    $range.daterangepicker({
        startDate: $range.data('start'),
        endDate: $range.data('end'),
        locale: {
            format: 'DD/MM/YYYY',
        },
    });

    $form.validate({
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
            var formData = new FormData($form[0]);

            formData.append('inscription', $('#inscription').val());
            formData.append('range', $('#range').val());

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
                        }, 2000);
                    } else {
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
