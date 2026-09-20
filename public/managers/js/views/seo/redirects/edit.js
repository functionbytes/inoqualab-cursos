$(document).ready(function () {

    $('#formRedirect').on('submit', function (e) {
        e.preventDefault();
        var $btn = $(this).find('[type=submit]');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Guardando...');
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                toastr.success(res.message, 'Éxito');
                $btn.prop('disabled', false).html('Guardar cambios');
            },
            error: function (xhr) {
                $btn.prop('disabled', false).html('Guardar cambios');
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    $.each(errors, function (field, messages) {
                        toastr.error(messages[0]);
                    });
                } else {
                    toastr.error('Error al actualizar la redirección.');
                }
            }
        });
    });

    $('.delete-btn').on('click', function () {
        $('#delete-modal .modal-title').text($(this).data('title'));
        $('#delete-form').attr('action', $(this).data('url'));
    });
});
