$(function () {

    var $page = $('#users-certificates-index');
    var flashSuccess = $page.data('flash-success');
    var flashError = $page.data('flash-error');
    if (flashSuccess) { toastr.success(flashSuccess); }
    if (flashError) { toastr.error(flashError); }

    // ── Filters modal ────────────────────────────────────────────────────────
    $('#applyFiltersBtn').on('click', function () {
        $('#filterCourse').val($('#modalCourse').val());
        $('#filters-modal').modal('hide');
        $('#searchForm').submit();
    });

});
