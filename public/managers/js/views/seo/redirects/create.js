$(document).ready(function () {

    $('#formRedirect').on('submit', function (e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('[type=submit]');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Guardando...');
        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize(),
            success: function (res) {
                toastr.success(res.message, 'Éxito');
                setTimeout(function () {
                    window.location.href = $form.data('redirect-index-url');
                }, 800);
            },
            error: function (xhr) {
                $btn.prop('disabled', false).html('Crear redirección');
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    $.each(errors, function (field, messages) {
                        toastr.error(messages[0]);
                    });
                } else {
                    toastr.error('Error al crear la redirección.');
                }
            }
        });
    });
});
