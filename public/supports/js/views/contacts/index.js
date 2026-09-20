$(function () {

    var bulkUrl = $('#contacts-list').data('bulk-url');

    // ── Filters modal ────────────────────────────────────────────────────────
    $('#applyFiltersBtn').on('click', function () {
        $('#filterReviewed').val($('#modalReviewed').val());
        $('#filters-modal').modal('hide');
        $('#searchForm').submit();
    });

    // ── Bulk selection ───────────────────────────────────────────────────────
    BulkActions.init({
        url: bulkUrl,
        entityLabel: 'contacto(s)',
    });

});
