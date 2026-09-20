$(document).ready(function () {

    var $form = $('#formSeoMeta');

    $form.on('submit', function (e) {
        e.preventDefault();
    });

    // Contadores de caracteres
    function charCounter(inputId, countId) {
        var $el = $('#' + inputId);
        $('#' + countId).text($el.val().length);
        $el.on('input', function () {
            $('#' + countId).text(this.value.length);
        });
    }
    charCounter('title', 'title-count');
    charCounter('description', 'description-count');

    // Vista previa de imagen OG
    $('#og_image').on('input', function () {
        var url = $(this).val().trim();
        if (url) {
            $('#og-image-preview img').attr('src', url);
            $('#og-image-preview').removeClass('d-none');
        } else {
            $('#og-image-preview').addClass('d-none');
        }
    }).trigger('input');

    // Validar JSON
    $('#btn-validate-json').on('click', function () {
        var val = $('#schema_custom').val().trim();
        var $msg = $('#json-validation-msg');

        if (!val) {
            $msg.text('(vacío)').attr('class', 'small text-muted');
            return;
        }

        try {
            JSON.parse(val);
            $msg.text('JSON válido').attr('class', 'small text-success');
        } catch (e) {
            $msg.text('JSON inválido: ' + e.message).attr('class', 'small text-danger');
        }
    });

    // Guardar vía AJAX
    $('#btn-save-meta').on('click', function () {
        var $btn = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Guardando...');

        // Limpiar errores previos
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        var data = {};
        $form.find('input, textarea, select').each(function () {
            if (this.name) data[this.name] = $(this).val();
        });

        $.ajax({
            url: $form.data('update-url'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: Object.assign(data, { _method: 'PUT' }),
            success: function (response) {
                toastr.success(response.message ?? 'Cambios guardados correctamente');
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    $.each(xhr.responseJSON.errors, function (field, messages) {
                        var $field = $('[name="' + field + '"]');
                        $field.addClass('is-invalid');
                        $field.closest('.col-12, .col-md-6').find('.invalid-feedback').first().text(messages[0]);
                    });
                    toastr.error('Corrige los errores del formulario');
                } else {
                    toastr.error('Error al guardar los cambios');
                }
            },
            complete: function () {
                $btn.prop('disabled', false).html('Guardar cambios');
            }
        });
    });

});
