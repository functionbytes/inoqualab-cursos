$(function () {
    var page = $('#bundlesPage');
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    var flashSuccess = page.data('flash-success');
    var flashError = page.data('flash-error');
    if (flashSuccess) { toastr.success(flashSuccess); }
    if (flashError) { toastr.error(flashError); }

    // ── Filters modal ────────────────────────────────────────────────────────
    function initSettingsBundlesTable() {
        FilterToolbar.init({
        fields: { filterAvailable: 'popover_Available' },
    });
        BulkActions.init({
        url: page.data('bulk-action-url'),
        entityLabel: 'paquete(s)',
    });
    }

    initSettingsBundlesTable();

    AjaxTable.init({ onLoaded: initSettingsBundlesTable });

    // ── Bulk selection ───────────────────────────────────────────────────────

    // ── Toggle disponibilidad individual ────────────────────────────────────
    $(document).on('click', '.btn-toggle-available', function () {
        var slack = $(this).data('slack');

        $.ajax({
            url: page.data('toggle-url'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: { slack: slack },
            success: function (res) {
                if (res.success) {
                    toastr.success(res.message, 'Listo', { positionClass: 'toast-bottom-right', progressBar: true, closeButton: true });
                    setTimeout(function () { location.reload(); }, 1200);
                }
            },
            error: function () {
                toastr.error('Error al cambiar el estado.');
            }
        });
    });

    // ── Eliminar individual via modal ────────────────────────────────────────
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $('#delete-modal .modal-title').text($btn.data('title'));
        $('#delete-form').attr('action', $btn.data('url'));
        $('#delete-modal').modal('show');
    });
});
