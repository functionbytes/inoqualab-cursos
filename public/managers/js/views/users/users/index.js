$(function () {

    var $page = $('#users-index');
    var config = $page.data('config') || {};

    var flashSuccess = $page.data('flash-success');
    var flashError = $page.data('flash-error');
    if (flashSuccess) { toastr.success(flashSuccess); }
    if (flashError) { toastr.error(flashError); }

    // ── Filters modal ────────────────────────────────────────────────────────
    function initUsersUsersTable() {
        FilterToolbar.init({
        fields: { filterRole: 'popover_Role' },
    });
        BulkActions.init({
        url: config.routes.bulkAction,
        entityLabel: 'usuario(s)',
    });
    }

    initUsersUsersTable();

    AjaxTable.init({ onLoaded: initUsersUsersTable });

    // ── Bulk selection ───────────────────────────────────────────────────────

    // ── Eliminar individual vía modal ────────────────────────────────────────
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $('#delete-modal .modal-title').text($btn.data('title'));
        $('#delete-form').attr('action', $btn.data('url'));
        $('#delete-modal').modal('show');
    });

});
