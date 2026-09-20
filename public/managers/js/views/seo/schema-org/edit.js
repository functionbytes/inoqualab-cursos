$(function () {

    var templateUrl = $('#formSchema').data('template-url-template');
    var validateUrl = $('#formSchema').data('validate-url');

    // ── Cargar plantilla al cambiar tipo ─────────────────────────────────────
    $('#schema_type').on('change', function () {
        var type = $(this).val();

        if (!type) return;

        $.getJSON(templateUrl.replace(':type', encodeURIComponent(type)), function (data) {
            if (data && data.template) {
                $('#schema_custom').val(
                    JSON.stringify(data.template, null, 2)
                );
                showValidationResult('Plantilla cargada para ' + type, 'info');
            }
        }).fail(function () {
            toastr.error('No se pudo cargar la plantilla.');
        });
    });

    // ── Validar JSON ─────────────────────────────────────────────────────────
    $('#btn-validate-schema').on('click', function () {
        var content = $('#schema_custom').val().trim();

        if (!content) {
            showValidationResult('El campo está vacío.', 'warning');
            return;
        }

        // Validación local rápida
        try {
            JSON.parse(content);
        } catch (e) {
            showValidationResult('JSON inválido: ' + e.message, 'danger');
            return;
        }

        // Validación en servidor
        $.ajax({
            url: validateUrl,
            method: 'POST',
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: JSON.stringify({ schema: content }),
            success: function (res) {
                if (res.valid) {
                    showValidationResult('JSON válido. ' + (res.message || ''), 'success');
                } else {
                    var errors = Array.isArray(res.errors) ? res.errors.join(', ') : (res.message || 'Error de validación');
                    showValidationResult('Errores: ' + errors, 'danger');
                }
            },
            error: function (xhr) {
                showValidationResult(xhr.responseJSON?.message ?? 'Error al validar en servidor.', 'danger');
            }
        });
    });

    function showValidationResult(message, type) {
        var iconMap = {
            success: 'fa-circle-check text-success',
            danger: 'fa-circle-xmark text-danger',
            warning: 'fa-triangle-exclamation text-warning',
            info: 'fa-circle-info text-primary',
        };
        $('#schema-validation-result').html(
            '<small class="' + (type === 'danger' ? 'text-danger' : type === 'success' ? 'text-success' : 'text-muted') + '">' +
            '<i class="fas ' + (iconMap[type] || 'fa-circle-info text-muted') + ' me-1"></i>' +
            message + '</small>'
        );
    }

});
