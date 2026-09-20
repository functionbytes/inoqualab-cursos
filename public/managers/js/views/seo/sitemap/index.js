$(document).ready(function () {

    // Limpiar caché
    $('#btn-clear-cache').on('click', function () {
        var $btn = $(this);
        $btn.html('<span class="spinner-border spinner-border-sm me-1"></span>Limpiando...').prop('disabled', true);

        $.ajax({
            url: $btn.data('clear-cache-url'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                toastr.success(response.message ?? 'Caché limpiada correctamente');
                setTimeout(function () { window.location.reload(); }, 800);
            },
            error: function (xhr) {
                var msg = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Error al limpiar la caché';
                toastr.error(msg);
            },
            complete: function () {
                $btn.html('Limpiar caché').prop('disabled', false);
            }
        });
    });

    // Forzar regeneración
    $('#btn-generate').on('click', function () {
        var $btn = $(this);
        $btn.html('<span class="spinner-border spinner-border-sm me-1"></span>Regenerando...').prop('disabled', true);

        $.ajax({
            url: $btn.data('generate-url'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                toastr.success(response.message ?? 'Sitemap regenerado correctamente');
                setTimeout(function () { window.location.reload(); }, 800);
            },
            error: function (xhr) {
                var msg = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Error al regenerar el sitemap';
                toastr.error(msg);
            },
            complete: function () {
                $btn.html('Forzar regeneración').prop('disabled', false);
            }
        });
    });

});
