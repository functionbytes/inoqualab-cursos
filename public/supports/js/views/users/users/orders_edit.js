$(document).ready(function () {
    var $form = $('#formOrders');
    var $payment = $('#payment');

    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true,
    });

    var paymentDate = $payment.data('payment-date');
    if (paymentDate) {
        $('.datepicker').datepicker('setDate', paymentDate);
    }

    $form.validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            condition: {
                required: true,
            },
            method: {
                required: true,
            },
            payment: {
                required: function () {
                    return $('#condition').val() == 4;
                },
            },
        },
        messages: {
            condition: {
                required: 'Es necesario un estado.',
            },
            method: {
                required: 'Es necesario un estado.',
            },
            payment: {
                required: 'Es necesario una fecha.',
            },
        },
        submitHandler: function (form) {
            var formData = new FormData($form[0]);

            formData.append('slack', $('#slack').val());
            formData.append('condition', $('#condition').val());
            formData.append('methods', $('#method').val());
            formData.append('payment', $payment.val());

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
                            window.location.href = $form.data('view-url-template').replace(':id', response.data.slack);
                        }, 1500);
                    } else {
                        $submitButton.prop('disabled', false);

                        toastr.warning(response.message, 'Operación fallida', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right',
                        });

                        $('.errors').text(response.message).removeClass('d-none');
                    }
                },
                error: function (xhr) {
                    $submitButton.prop('disabled', false);

                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        var firstError = Object.values(errors)[0][0];

                        toastr.error(firstError, 'Datos inválidos', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right',
                        });
                    }
                },
            });
        },
    });
});
