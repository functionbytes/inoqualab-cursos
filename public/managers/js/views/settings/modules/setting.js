$(function () {
    var $form = $('#formModules');
    var updateUrl = $form.data('update-url');
    var moduleKeys = $form.data('module-keys') || [];

    $('#btnSave').on('click', function () {
        var $btn = $(this).prop('disabled', true).text('Guardando...');
        var data = {};

        // Checkboxes: enviar 0 si no están marcados
        moduleKeys.forEach(function (key) {
            data[key] = $('#' + key).is(':checked') ? 1 : 0;
        });

        data['_token'] = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            url: updateUrl,
            method: 'POST',
            data: data,
            success: function (res) {
                toastr.success(res.message);
                $btn.prop('disabled', false).text('Guardar cambios');
            },
            error: function (xhr) {
                toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Error al guardar.');
                $btn.prop('disabled', false).text('Guardar cambios');
            }
        });
    });
});
