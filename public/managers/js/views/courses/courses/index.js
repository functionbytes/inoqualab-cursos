$(function () {

    var $page = $('#courses-index');
    var config = $page.data('config') || {};

    var flashSuccess = $page.data('flash-success');
    if (flashSuccess) { toastr.success(flashSuccess); }

    // ── Filters modal ────────────────────────────────────────────────────────
    function initCoursesCoursesTable() {
        FilterToolbar.init({
        fields: { filterAvailable: 'popover_Available', filterWebsite: 'popover_Website' },
    });
        BulkActions.init({
        url: config.routes.bulkAction,
        entityLabel: 'curso(s)',
    });
    }

    initCoursesCoursesTable();

    AjaxTable.init({ onLoaded: initCoursesCoursesTable });

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
