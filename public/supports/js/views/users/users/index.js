$(function () {
    var $container = $('.searchable-container');

    var flashSuccess = $container.data('flash-success');
    if (flashSuccess) {
        toastr.success(flashSuccess);
    }

    $('#applyFiltersBtn').on('click', function () {
        $('#filterRole').val($('#modalRole').val());
        $('#filters-modal').modal('hide');
        $('#searchForm').submit();
    });

    BulkActions.init({
        url: $container.data('bulkUrl'),
        entityLabel: $container.data('bulkEntityLabel'),
    });
});
