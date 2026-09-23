$(function () {
    var page = $('#slidersPage');

    var flashSuccess = page.data('flash-success');
    var flashError = page.data('flash-error');
    if (flashSuccess) { toastr.success(flashSuccess); }
    if (flashError) { toastr.error(flashError); }

    // ── Filters + bulk selection ─────────────────────────────────────────────
    function initSettingsSlidersTable() {
        FilterToolbar.init({
            fields: { filterAvailable: 'popover_Available' },
        });
        BulkActions.init({
            url: page.data('bulk-action-url'),
            entityLabel: 'banner(s)',
        });
    }

    initSettingsSlidersTable();

    AjaxTable.init({ onLoaded: initSettingsSlidersTable });

    // ── Eliminar individual vía modal ────────────────────────────────────────
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $('#delete-modal .modal-title').text($btn.data('title'));
        $('#delete-form').attr('action', $btn.data('url'));
        $('#delete-modal').modal('show');
    });
});
