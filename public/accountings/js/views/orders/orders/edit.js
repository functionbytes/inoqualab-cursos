$(document).on('submit', '#formOrders', function (e) {
    e.preventDefault();
});

$(document).ready(function () {
    const $form = $('#formOrders');
    const updateUrl = $form.data('update-url');
    const viewUrlTemplate = $form.data('view-url-template');
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
                required: 'Es necesario una fecha.',
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
                        const url = viewUrlTemplate.replace(':id', response.data.slack);

                        $('#view-modal').modal('show');
                        $('#view-link').attr('href', url);
                    } else {
                        toastr.warning('Se ha generado un error.', 'Operación fallida', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right',
                        });

                        $('.errors').removeClass('d-none');
                    }
                },
            });
        },
    });
});
