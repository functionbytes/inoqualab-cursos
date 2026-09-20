$(document).ready(function () {

    $('#btn-submit-indexnow').on('click', function () {
        var rawText = $('#urls-input').val().trim();

        if (!rawText) {
            toastr.warning('Escribe al menos una URL antes de enviar.', 'Aviso', {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-bottom-right'
            });
            return;
        }

        var urls = rawText.split('\n')
            .map(function (u) { return u.trim(); })
            .filter(function (u) { return u.length > 0; });

        if (!urls.length) {
            toastr.warning('No se encontraron URLs válidas.', 'Aviso', {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-bottom-right'
            });
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Enviando...');

        $.ajax({
            url: $btn.data('submit-url'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { urls: urls },
            success: function (response) {
                toastr.success(response.message ?? 'URLs enviadas correctamente.', 'Exito', {
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-bottom-right'
                });
                $('#urls-input').val('');
            },
            error: function (xhr) {
                var message = 'Error al enviar las URLs.';
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    var firstKey = Object.keys(errors)[0];
                    message = errors[firstKey][0];
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                toastr.error(message, 'Error', {
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-bottom-right'
                });
            },
            complete: function () {
                $btn.prop('disabled', false).html('Enviar a IndexNow');
            }
        });
    });

});
