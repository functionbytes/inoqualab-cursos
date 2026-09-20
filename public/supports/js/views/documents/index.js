$(function () {

    var bulkUrl = $('#documents-list').data('bulk-url');

    // ── Filters modal ────────────────────────────────────────────────────────
    $('#applyFiltersBtn').on('click', function () {
        $('#filterAvailable').val($('#modalAvailable').val());
        $('#filters-modal').modal('hide');
        $('#searchForm').submit();
    });

    // ── Bulk selection ───────────────────────────────────────────────────────
    BulkActions.init({
        url: bulkUrl,
        entityLabel: 'documento(s)',
    });

});
