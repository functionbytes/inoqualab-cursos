$(document).ready(function () {
    $(document).on('change', '#wompi_sandbox', function () {
        var isSandbox = $(this).is(':checked');
        $('.wompi-credentials-group[data-env="sandbox"]').toggleClass('d-none', !isSandbox);
        $('.wompi-credentials-group[data-env="production"]').toggleClass('d-none', isSandbox);
    });

    $('#formPayments').on('submit', function (e) {
        e.preventDefault();

        var updateUrl = $(this).data('update-url');

        $.ajax({
            type: 'POST',
            url: updateUrl,
            data: $(this).serialize(),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                if (response.success) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr) {
                toastr.error('Error al guardar la configuración');
            }
        });
    });

    $('#copyWebhook').on('click', function () {
        var url = $('#webhookUrl').val();
        navigator.clipboard.writeText(url).then(function () {
            toastr.info('URL copiada al portapapeles');
        });
    });
});
