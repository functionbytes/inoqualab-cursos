$(function () {

    var $page = $('#courses-categories-index');
    var config = $page.data('config') || {};

    var flashSuccess = $page.data('flash-success');
    if (flashSuccess) { toastr.success(flashSuccess); }

    // ── Filters modal ────────────────────────────────────────────────────────
    function initCoursesCategoriesTable() {
        FilterToolbar.init({
        fields: { filterAvailable: 'popover_Available' },
    });
        BulkActions.init({
        url: config.routes.bulkAction,
        entityLabel: 'categoria(s)',
    });
    }

    initCoursesCategoriesTable();

    AjaxTable.init({ onLoaded: initCoursesCategoriesTable });

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
