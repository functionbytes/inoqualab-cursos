$(function () {
    $('#applyFiltersBtn').on('click', function () {
        $('#filterYear').val($('#modalYear').val());
        $('#filterCourse').val($('#modalCourse').val());
        $('#filters-modal').modal('hide');
        $('#searchForm').submit();
    });
});
