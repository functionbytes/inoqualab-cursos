$(function () {
    var page = $('#rulesPage');
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    // ── Tabla: filtros + selección masiva (se re-bindean tras cada carga AJAX) ──
    function initRulesTable() {
        FilterToolbar.init({
            fields: { filterStatus: 'popover_status', filterConfidence: 'popover_confidence' },
        });
        BulkActions.init({
            url: page.attr('data-bulk-action-url'),
            entityLabel: 'regla(s)',
        });
    }

    initRulesTable();

    AjaxTable.init({ onLoaded: initRulesTable });

    // ── Modal nueva regla ───────────────────────────────────────────────────
    var $enterprise = $('#rule-enterprise');
    var $confidence = $('#rule-confidence');

    $enterprise.select2({
        placeholder: 'Buscar empresa...',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#rule-modal'),
        ajax: {
            url: page.attr('data-enterprises-url'),
            dataType: 'json',
            delay: 300,
            data: function (params) { return { q: params.term || '' }; },
            processResults: function (data) { return { results: data }; },
            cache: true,
        },
    });

    $confidence.on('input', function () {
        $('#confidence-display').text($(this).val() + '%');
    });

    $('#rule-modal').on('hidden.bs.modal', function () {
        $enterprise.val(null).trigger('change');
        $confidence.val(90);
        $('#confidence-display').text('90%');
    });

    $('#save-rule-btn').on('click', function () {
        var enterpriseId = $enterprise.val();
        if (!enterpriseId) { toastr.warning('Selecciona una empresa.'); return; }

        var $btn = $(this).prop('disabled', true).text('Guardando...');
        $.ajax({
            url: page.attr('data-store-url'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            contentType: 'application/json',
            data: JSON.stringify({ enterprise_id: enterpriseId, min_confidence: $confidence.val() }),
        })
            .done(function (res) {
                if (!res.success) { toastr.error(res.message); return; }
                toastr.success(res.message);
                $('#rule-modal').modal('hide');
                setTimeout(function () { location.reload(); }, 700);
            })
            .fail(function () { toastr.error('Error al guardar la regla.'); })
            .always(function () { $btn.prop('disabled', false).text('Agregar regla'); });
    });

    // ── Activar / desactivar ────────────────────────────────────────────────
    $(document).on('click', '.toggle-btn', function (e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('data-url'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-HTTP-Method-Override': 'PATCH' },
        })
            .done(function (res) {
                toastr.success(res.message || 'Estado actualizado.');
                setTimeout(function () { location.reload(); }, 700);
            })
            .fail(function () { toastr.error('Error al cambiar el estado.'); });
    });

    // ── Eliminar ────────────────────────────────────────────────────────────
    $(document).on('click', '.delete-btn', function (e) {
        e.preventDefault();
        $('#delete-modal-title').text($(this).attr('data-title'));
        $('#confirm-delete-btn').attr('data-url', $(this).attr('data-url'));
        $('#delete-modal').modal('show');
    });

    $('#confirm-delete-btn').on('click', function () {
        var $btn = $(this).prop('disabled', true);
        $.ajax({
            url: $btn.attr('data-url'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-HTTP-Method-Override': 'DELETE' },
        })
            .done(function (res) {
                if (!res.success) { toastr.error(res.message); return; }
                toastr.success(res.message);
                $('#delete-modal').modal('hide');
                setTimeout(function () { location.reload(); }, 700);
            })
            .fail(function () { toastr.error('Error al eliminar la regla.'); })
            .always(function () { $btn.prop('disabled', false); });
    });
});
