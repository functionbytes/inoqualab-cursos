$(document).ready(function () {
    var page = $('#analyticsNotificationsPage');

    // Añadir fila — quita + de la última fila actual, agrega nueva fila con +
    $(document).on('click', '.add-email', function () {
        var $container = $(this).closest('.emails-container');
        var name = $container.data('name');

        $(this).remove();

        var $row = $('<div class="mb-2 email-row"><div class="input-group">' +
            '<input type="email" class="form-control" name="' + name + '" placeholder="correo@ejemplo.com">' +
            '<button type="button" class="btn btn-info remove-email"><i class="fas fa-times"></i></button>' +
            '<button type="button" class="btn btn-outline-secondary add-email"><i class="fas fa-plus"></i></button>' +
            '</div></div>');
        $container.append($row);
        $row.find('input').focus();
    });

    // Eliminar fila — limpia si es la última, sino elimina y asegura + en la nueva última
    $(document).on('click', '.remove-email', function () {
        var $container = $(this).closest('.emails-container');
        var $rows = $container.find('.email-row');

        if ($rows.length > 1) {
            var $row = $(this).closest('.email-row');
            var wasLast = $row.is(':last-child');
            $row.remove();
            if (wasLast) {
                $container.find('.email-row:last .input-group').append(
                    '<button type="button" class="btn btn-outline-secondary add-email"><i class="fas fa-plus"></i></button>'
                );
            }
        } else {
            $container.find('.email-row input').val('');
        }
    });

    var flashSuccess = page.data('flash-success');
    if (flashSuccess) {
        toastr.success(flashSuccess);
    }
});
