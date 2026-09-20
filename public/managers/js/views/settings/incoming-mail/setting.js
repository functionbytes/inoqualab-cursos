$(document).ready(function () {
    $('#formIncomingMail').on('submit', function (e) {
        e.preventDefault();

        var updateUrl = $(this).data('update-url');
        var btn = $('#btnSaveIncomingMail');
        btn.prop('disabled', true).html('Guardando...');

        $.ajax({
            type: 'POST',
            url: updateUrl,
            data: $(this).serialize(),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                if (response.success) {
                    toastr.success(response.message);
                    // Refrescar para que el badge ACTIVO se actualice
                    setTimeout(function () { location.reload(); }, 800);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function () {
                toastr.error('Error al guardar la configuración');
            },
            complete: function () {
                btn.prop('disabled', false).html('Guardar configuración');
            }
        });
    });
});
