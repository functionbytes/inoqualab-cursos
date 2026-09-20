$(document).on('submit', '#formInvoices', function (e) {
    e.preventDefault();
});

$(document).ready(function () {
    const $form = $('#formInvoices');
    const storeUrl = $form.data('store-url');
    const viewUrlTemplate = $form.data('view-url-template');

    $('.daterange').daterangepicker();

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
            distributor: {
                required: true,
            },
        },
        messages: {
            condition: {
                required: 'Es necesario un estado.',
            },
            method: {
                required: 'Es necesario un estado.',
            },
            distributor: {
                required: 'Es necesario un distribuidor.',
            },
        },
        submitHandler: function (form) {
            const formData = new FormData(form);
            formData.append('distributor', $('#distributor').val());
            formData.append('condition', $('#condition').val());
            formData.append('methods', $('#method').val());
            formData.append('range', $('#range').val());

            $.ajax({
                url: storeUrl,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                type: 'POST',
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {
                    if (response.success == true) {
                        const url = viewUrlTemplate.replace(':id', response.data);

                        $('#view-modal').modal('show');
                        $('#view-link').attr('href', url);
                    } else {
                        toastr.warning(response.message, 'Operación fallida', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right',
                        });

                        $('.errors').text(response.message);
                        $('.errors').removeClass('d-none');
                    }
                },
            });
        },
    });
});
