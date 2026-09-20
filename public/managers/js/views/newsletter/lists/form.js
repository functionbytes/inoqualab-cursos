$(function () {
    var $form = $('#formList');
    var isNew = $form.data('is-new');
    var saveUrl = $form.data('save-url');
    var saveMethod = $form.data('save-method');

    $('#btnSave').on('click', function () {
        var name = $.trim($('#fieldName').val());
        if (!name) {
            toastr.warning('El nombre es obligatorio.');
            return;
        }

        var $btn = $(this).prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...'
        );

        $.ajax({
            url: saveUrl,
            method: saveMethod,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
                name: name,
                description: $('#fieldDescription').val(),
                is_active: $('#fieldActive').is(':checked') ? 1 : 0,
            },
            success: function (response) {
                if (response.redirect) {
                    window.location.href = response.redirect;
                } else {
                    toastr.success(response.message);
                    $btn.prop('disabled', false).text('Guardar cambios');
                }
            },
            error: function (xhr) {
                $btn.prop('disabled', false).text(isNew ? 'Crear lista' : 'Guardar cambios');
                if (xhr.status === 422 && xhr.responseJSON) {
                    var errors = xhr.responseJSON.errors || {};
                    $.each(errors, function (field, messages) { toastr.error(messages[0]); });
                    if (xhr.responseJSON.message && !Object.keys(errors).length) {
                        toastr.error(xhr.responseJSON.message);
                    }
                } else {
                    toastr.error('Ocurrió un error al guardar.');
                }
            },
        });
    });
});
