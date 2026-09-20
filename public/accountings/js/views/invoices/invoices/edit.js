$(document).on('submit', '#formInvoices', function (e) {
    e.preventDefault();
});

$(document).ready(function () {
    const $form = $('#formInvoices');
    const updateUrl = $form.data('update-url');
    const distributorInvoicesUrl = $form.data('distributor-invoices-url');
    const paymentDate = $form.data('payment-date');

    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true,
    });

    $('.datepicker').datepicker('setDate', paymentDate);

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
                required: 'Es necesario una fecha',
            },
        },
        submitHandler: function (form) {
            const formData = new FormData(form);
            formData.append('slack', $('#slack').val());
            formData.append('condition', $('#condition').val());
            formData.append('methods', $('#method').val());
            formData.append('payment', $('#payment').val());

            $.ajax({
                url: updateUrl,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                type: 'POST',
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {
                    if (response.success == true) {
                        toastr.success('Se ha editado correctamente la factura.', 'Operación exitosa', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right',
                        });

                        // accounting.distributors.invoices exige {slack}; el distributor solo
                        // se conoce tras la respuesta AJAX, así que se arma con la URL base
                        // (route('...', '') lanzaba "Missing required parameter").
                        setTimeout(function () {
                            window.location.href = distributorInvoicesUrl + '/' + response.data.distributor;
                        }, 2000);
                    } else {
                        toastr.warning(response.error, 'Operación fallida', {
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
