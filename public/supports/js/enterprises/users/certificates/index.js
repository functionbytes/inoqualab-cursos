$(function () {
    $('#applyFiltersBtn').on('click', function () {
        $('#filterCourse').val($('#modalCourse').val());
        $('#filters-modal').modal('hide');
        $('#searchForm').submit();
    });
});
