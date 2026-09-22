$(function () {
    var page = $('#slidersPage');
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    var flashSuccess = page.data('flash-success');
    var flashError = page.data('flash-error');
    if (flashSuccess) { toastr.success(flashSuccess); }
    if (flashError) { toastr.error(flashError); }

    // ── Filters modal ────────────────────────────────────────────────────────
    function initSettingsSlidersTable() {
        FilterToolbar.init({
        fields: { filterAvailable: 'popover_Available' },
    });
    }

    initSettingsSlidersTable();

    AjaxTable.init({ onLoaded: initSettingsSlidersTable });

    // ── Bulk selection ───────────────────────────────────────────────────────
    function updateBulkToolbar() {
        var count = $('.bulk-checkbox:checked').length;
        $('[data-bulk-count]').text(count);
        count > 0 ? $('#bulk-toolbar').removeClass('d-none') : $('#bulk-toolbar').addClass('d-none');
    }

    function getChecked() {
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

    $('#bulk-modal').on('hide.bs.modal', function () {
        $('#bulk-action-select').val('');
        $('#btn-bulk-apply').prop('disabled', false).text('Aplicar');
    });

    $('#btn-bulk-apply').on('click', function () {
        var action = $('#bulk-action-select').val();
        var ids = getChecked();

        if (!action) { toastr.warning('Selecciona una acción.'); return; }
        if (!ids.length) { toastr.warning('Selecciona al menos un banner.'); return; }
        if (action === 'delete' && !confirm('¿Eliminar los ' + ids.length + ' banner(es)?')) return;

        $('#btn-bulk-apply').prop('disabled', true).text('Procesando...');

        $.ajax({
            url: page.data('bulk-action-url'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            contentType: 'application/json',
            data: JSON.stringify({ action: action, ids: ids }),
            success: function (res) {
                $('#bulk-modal').modal('hide');
                toastr.success(res.message || 'Acción aplicada.');
                setTimeout(function () { location.reload(); }, 700);
            },
            error: function (xhr) {
                toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Error al procesar.');
                $('#btn-bulk-apply').prop('disabled', false).text('Aplicar');
            }
        });
    });

    // ── Eliminar individual vía modal ────────────────────────────────────────
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $('#delete-modal .modal-title').text($btn.data('title'));
        $('#delete-form').attr('action', $btn.data('url'));
        $('#delete-modal').modal('show');
    });
});
