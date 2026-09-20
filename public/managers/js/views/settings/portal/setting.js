$(document).ready(function () {
    $('#formPortal').on('submit', function (e) {
        e.preventDefault();

        var updateUrl = $(this).data('update-url');
        var $submitButton = $('#formPortal button[type="submit"]');
        $submitButton.prop('disabled', true);

        var data = { _token: $('meta[name="csrf-token"]').attr('content') };

        $('#formPortal input[type="radio"]:checked').each(function () {
            data[this.name] = this.value;
        });

        $.ajax({
            url: updateUrl,
            type: 'POST',
            data: data,
            success: function (response) {
                if (response.success === true) {
                    toastr.success(response.message, 'Operación exitosa', {
                        closeButton: true, progressBar: true, positionClass: 'toast-bottom-right'
                    });
                }
            },
            error: function (xhr) {
                var msg = 'No se pudo guardar la configuración.';
                if (xhr.responseJSON && xhr.responseJSON.message) { msg = xhr.responseJSON.message; }
                toastr.error(msg, 'Error', {
                    closeButton: true, progressBar: true, positionClass: 'toast-bottom-right'
                });
            },
            complete: function () {
                $submitButton.prop('disabled', false);
            }
        });
    });
});
