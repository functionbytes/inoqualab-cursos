$(function () {

    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    var $config = $('#logs-config');
    var bulkDestroyUrl = $config.data('bulk-destroy-url');
    var createRedirectUrl = $config.data('create-redirect-url');
    var clearUrl = $config.data('clear-url');

    $('#formCreateRedirect').on('submit', function (e) {
        e.preventDefault();
    });

    // ── Bulk selection ────────────────────────────────────────────────────────
    function updateBulkToolbar() {
        var count = $('.bulk-checkbox:checked').length;
        $('[data-bulk-count]').text(count);
        count > 0 ? $('#bulk-toolbar').removeClass('d-none') : $('#bulk-toolbar').addClass('d-none');
    }

    function getSelectedIds() {
        return $('.bulk-checkbox:checked').map(function () { return $(this).val(); }).get();
    }

    $('#select-all').on('change', function () {
        $('.bulk-checkbox').prop('checked', $(this).prop('checked'));
        updateBulkToolbar();
    });

    $(document).on('change', '.bulk-checkbox', function () {
        var total = $('.bulk-checkbox').length;
        var checked = $('.bulk-checkbox:checked').length;
        $('#select-all').prop('indeterminate', checked > 0 && checked < total);
        $('#select-all').prop('checked', checked === total);
        updateBulkToolbar();
    });

    // ── Bulk modal ────────────────────────────────────────────────────────────
    $('#bulk-modal').on('hide.bs.modal', function () {
        $('#bulk-action-select').val('');
        $('#bulk-apply-btn').prop('disabled', false).text('Aplicar');
    });

    $('#bulk-apply-btn').on('click', function () {
        var action = $('#bulk-action-select').val();
        var ids = getSelectedIds();

        if (!action) { toastr.warning('Selecciona una accion.'); return; }
        if (!ids.length) { toastr.warning('Selecciona al menos un registro.'); return; }

        if (action === 'delete' && !confirm('¿Eliminar los ' + ids.length + ' registro(s)?')) return;

        $('#bulk-apply-btn').prop('disabled', true).text('Procesando...');

        $.ajax({
            url: bulkDestroyUrl,
            method: 'POST',
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: JSON.stringify({ action: action, ids: ids }),
            success: function (res) {
                $('#bulk-modal').modal('hide');
                toastr.success(res.message ?? 'Registros eliminados.');
                setTimeout(function () { location.reload(); }, 800);
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? 'Error al procesar.');
                $('#bulk-apply-btn').prop('disabled', false).text('Aplicar');
            }
        });
    });

    // ── Crear redirect desde 404 ──────────────────────────────────────────────
    $(document).on('click', '.btn-create-redirect', function (e) {
        e.preventDefault();
        $('#log-id').val($(this).data('id'));
        $('#redirect-source').val($(this).data('path'));
        $('#redirect-target').val('').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('#modalCreateRedirect').modal('show');
    });

    $('#btn-save-log-redirect').on('click', function () {
        var $btn = $(this).prop('disabled', true).text('Creando...');

        $.ajax({
            url: createRedirectUrl,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: {
                log_id: $('#log-id').val(),
                source_path: $('#redirect-source').val(),
                target_path: $('#redirect-target').val(),
                status_code: $('#redirect-code').val(),
            },
            success: function (response) {
                toastr.success(response.message ?? 'Redirect creado correctamente');
                $('#modalCreateRedirect').modal('hide');
                setTimeout(function () { location.reload(); }, 800);
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    $.each(xhr.responseJSON.errors, function (field, messages) {
                        $('#redirect-' + field.replace('_path', '')).addClass('is-invalid')
                            .next('.invalid-feedback').text(messages[0]);
                    });
                } else {
                    toastr.error('Error al crear el redirect');
                }
            },
            complete: function () {
                $btn.prop('disabled', false).text('Crear redirect');
            }
        });
    });

    // ── Marcar resuelto ───────────────────────────────────────────────────────
    $(document).on('click', '.btn-mark-resolved', function (e) {
        e.preventDefault();
        var $el = $(this);

        $.ajax({
            url: $el.data('url'),
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (response) {
                toastr.success(response.message ?? 'Marcado como resuelto');
                $el.closest('tr').fadeOut(400, function () { $(this).remove(); });
            },
            error: function () {
                toastr.error('Error al marcar como resuelto');
            }
        });
    });

    // ── Limpiar registros viejos ──────────────────────────────────────────────
    $('#btn-clean-old').on('click', function () {
        if (!confirm('¿Eliminar todos los registros 404 con más de 90 días de antigüedad?')) return;

        var $btn = $(this).prop('disabled', true).text('Limpiando...');

        $.ajax({
            url: clearUrl,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (response) {
                toastr.success(response.message ?? 'Registros eliminados');
                setTimeout(function () { location.reload(); }, 800);
            },
            error: function () {
                toastr.error('Error al limpiar los registros');
            },
            complete: function () {
                $btn.prop('disabled', false).text('Limpiar registros (+90 días)');
            }
        });
    });

});
