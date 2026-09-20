$(document).on('submit', '#formInvoices', function (e) {
    e.preventDefault();
});

$(document).ready(function () {
    var urls = $('#formInvoices').data('urls');

    $('#formInvoices').validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            invoice_default: {
                required: true,
                minlength: 1,
                maxlength: 4,
            },
            invoice_days: {
                required: true,
                number: true,
                min: 0,
                max: 365,
            },
        },
        messages: {
            invoice_default: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 1 caracter',
                maxlength: 'Debe contener al menos 4 caracter',
            },
            invoice_days: {
                required: 'El parametro es necesario.',
                number: 'Solo se puede ingresar números.',
                min: 'Debe mayor o igual a 0',
                max: 'Debe ser menor o igual a 365',
            },
        },
        submitHandler: function (form) {
            var $form = $('#formInvoices');
            var formData = new FormData($form[0]);
            var invoiceDefault = $('#invoice_default').val();
            var invoiceDays = $('#invoice_days').val();
            var notificationEmailEnable = $('#invoices_notification_email_enable').is(':checked');

            formData.append('invoice_default', invoiceDefault);
            formData.append('invoice_days', invoiceDays);
            formData.append('invoices_notification_email_enable', notificationEmailEnable);

            var $submitButton = $('button[type="submit"]');
            $submitButton.prop('disabled', true);

            $.ajax({
                url: urls.update,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
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
                            positionClass: 'toast-bottom-right'
                        });

                        setTimeout(function () {
                            window.location.href = urls.dashboard;
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
