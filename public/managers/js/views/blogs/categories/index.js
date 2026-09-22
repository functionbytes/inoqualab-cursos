$(function () {

    var $page = $('#blogs-categories-index');
    var config = $page.data('config') || {};

    var flashSuccess = $page.data('flash-success');
    var flashError = $page.data('flash-error');
    if (flashSuccess) { toastr.success(flashSuccess); }
    if (flashError) { toastr.error(flashError); }

    // ── Filters modal ────────────────────────────────────────────────────────
    function initBlogsCategoriesTable() {
        FilterToolbar.init({
        fields: { filterAvailable: 'popover_Available' },
    });
        BulkActions.init({
        url: config.routes.bulkAction,
        entityLabel: 'categoria(s)',
    });
    }

    initBlogsCategoriesTable();

    AjaxTable.init({ onLoaded: initBlogsCategoriesTable });

    // ── Bulk selection ───────────────────────────────────────────────────────

    // ── Eliminar individual via modal ────────────────────────────────────────
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $('#delete-modal .modal-title').text($btn.data('title'));
        $('#delete-form').attr('action', $btn.data('url'));
        $('#delete-modal').modal('show');
    });

});
