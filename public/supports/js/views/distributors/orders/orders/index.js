$(function () {
    var $container = $('.searchable-container');

    var flashSuccess = $container.data('flash-success');
    if (flashSuccess) {
        toastr.success(flashSuccess);
    }

    $('#applyFiltersBtn').on('click', function () {
        $('#filterCondition').val($('#modalCondition').val());
        $('#filterType').val($('#modalType').val());
        $('#filterMethods').val($('#modalMethods').val());
        $('#filters-modal').modal('hide');
        $('#searchForm').submit();
    });
});
