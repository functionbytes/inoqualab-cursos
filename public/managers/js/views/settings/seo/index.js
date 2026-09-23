$(document).ready(function () {
    // Mostrar/ocultar clave IndexNow según el toggle
    $('#seo_indexnow_enabled').on('change', function () {
        $('#indexNowFields').toggleClass('d-none', !this.checked);
    });

    // Generar UUID para IndexNow
    $('#btn-gen-uuid').on('click', function () {
        var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = Math.random() * 16 | 0;
            return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
        });
        $('#seo_indexnow_key').val(uuid);
        toastr.info('Clave generada. Recuerda guardar los cambios.');
    });

    // Guardar genérico para cualquier formulario
    function saveForm($btn) {
        var formId = $btn.data('form');
        var url = $btn.data('url');
        var $form = $('#' + formId);

        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').text('');

        var data = {};
        $form.find('input, textarea, select').each(function () {
            if (!this.name) return;
            if (this.type === 'checkbox') {
                data[this.name] = this.checked ? '1' : '0';
            } else {
                data[this.name] = $(this).val();
            }
        });

        $btn.prop('disabled', true).prepend('<span class="spinner-border spinner-border-sm me-1"></span>');

        $.ajax({
            url: url,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: data,
            success: function (response) {
                toastr.success(response.message || 'Configuración guardada');
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    $.each(xhr.responseJSON.errors, function (field, messages) {
                        $form.find('[name="' + field + '"]')
                            .addClass('is-invalid')
                            .next('.invalid-feedback').text(messages[0]);
                    });
                    toastr.error('Corrige los errores del formulario');
                } else {
                    toastr.error('Error al guardar la configuración');
                }
            },
            complete: function () {
                $btn.prop('disabled', false).find('.spinner-border').remove();
            }
        });
    }

    $('#btn-save-general').on('click', function () { saveForm($(this)); });
    $('#btn-save-verifications').on('click', function () { saveForm($(this)); });
    $('#btn-save-robots').on('click', function () { saveForm($(this)); });
    $('#btn-save-llms').on('click', function () { saveForm($(this)); });
});
