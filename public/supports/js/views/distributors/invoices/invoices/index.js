$(function () {
    var $container = $('.searchable-container');

    var flashSuccess = $container.data('flash-success');
    if (flashSuccess) {
        toastr.success(flashSuccess);
    }

    $('#applyFiltersBtn').on('click', function () {
        $('#filterCondition').val($('#modalCondition').val());
        $('#filterMethod').val($('#modalMethod').val());
        $('#filters-modal').modal('hide');
        $('#searchForm').submit();
    });
});
