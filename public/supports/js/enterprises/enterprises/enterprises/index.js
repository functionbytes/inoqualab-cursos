$(function () {
    $('#applyFiltersBtn').on('click', function () {
        $('#filterAvailable').val($('#modalAvailable').val());
        $('#filters-modal').modal('hide');
        $('#searchForm').submit();
    });

    BulkActions.init({
        url: $('#searchForm').data('bulk-url'),
        entityLabel: 'empresa(s)',
    });
});
