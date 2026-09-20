$(document).ready(function () {

    var config = $('#mails-settings').data('config') || {};

    // Select2 AJAX para empresa
    $('#rule-enterprise').select2({
        placeholder: 'Buscar empresa...',
        allowClear: true,
        ajax: {
            url: config.routes.enterprises,
            dataType: 'json', delay: 300,
            data: function (p) { return { q: p.term || '' }; },
            processResults: function (d) { return { results: d }; },
            cache: true
        }
    });

    // Slider de confianza
    $('#rule-confidence').on('input', function () {
        $('#confidence-display').text($(this).val() + '%');
    });

    // Guardar nueva regla
    $('#save-rule-btn').on('click', function () {
        var enterpriseId = $('#rule-enterprise').val();
        var minConfidence = $('#rule-confidence').val();
        if (!enterpriseId) { toastr.warning('Selecciona una empresa.'); return; }

        var $btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Guardando...');
        $.ajax({
            url: config.routes.store,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            contentType: 'application/json',
            data: JSON.stringify({ enterprise_id: enterpriseId, min_confidence: minConfidence }),
            success: function (r) {
                $btn.prop('disabled', false).html('Agregar regla');
                if (!r.success) { toastr.error(r.message); return; }
                toastr.success(r.message);
                $('#empty-row').remove();
                var row = '<tr id="rule-row-' + r.rule.id + '">'
                    + '<td class="fw-semibold">' + $('<span>').text(r.rule.enterprise_title).html() + '</td>'
                    + '<td class="text-center"><span class="badge bg-primary rounded-3 py-1 px-2">≥ ' + r.rule.min_confidence + '%</span></td>'
                    + '<td class="text-center"><div class="form-check form-switch d-inline-block mb-0">'
                    + '<input class="form-check-input rule-toggle" type="checkbox" data-id="' + r.rule.id + '" checked></div></td>'
                    + '<td class="text-center"><div class="dropdown dropstart"><a href="#" class="text-muted" data-bs-toggle="dropdown">'
                    + '<i class="fas fa-ellipsis-vertical fs-5"></i></a><ul class="dropdown-menu">'
                    + '<li><a class="dropdown-item delete-rule-btn" href="#" data-id="' + r.rule.id + '">Eliminar</a></li>'
                    + '</ul></div></td></tr>';
                $('#rules-tbody').append(row);
                $('#rule-enterprise').val(null).trigger('change');
                $('#rule-confidence').val(90);
                $('#confidence-display').text('90%');
            },
            error: function () {
                $btn.prop('disabled', false).html('Agregar regla');
                toastr.error('Error al guardar la regla.');
            }
        });
    });

    // Toggle activo/inactivo
    $(document).on('change', '.rule-toggle', function () {
        var id = $(this).data('id');
        var $cb = $(this);
        $.ajax({
            url: config.routes.rulesBase + '/' + id + '/toggle',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-HTTP-Method-Override': 'PATCH',
            },
            success: function (r) {
                r.success ? toastr.success(r.message) : toastr.error(r.message);
                if (!r.success) $cb.prop('checked', !$cb.prop('checked'));
            },
            error: function () {
                toastr.error('Error al cambiar el estado.');
                $cb.prop('checked', !$cb.prop('checked'));
            }
        });
    });

    // Eliminar regla
    $(document).on('click', '.delete-rule-btn', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (!confirm('¿Eliminar esta regla?')) return;
        $.ajax({
            url: config.routes.rulesBase + '/' + id,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-HTTP-Method-Override': 'DELETE',
            },
            success: function (r) {
                if (r.success) {
                    toastr.success(r.message);
                    $('#rule-row-' + id).remove();
                    if ($('#rules-tbody tr').length === 0) {
                        $('#rules-tbody').html('<tr id="empty-row"><td colspan="4" class="text-center py-4 text-muted">No hay reglas configuradas</td></tr>');
                    }
                } else { toastr.error(r.message); }
            },
            error: function () { toastr.error('Error al eliminar la regla.'); }
        });
    });

});
