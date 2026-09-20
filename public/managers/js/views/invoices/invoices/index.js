$(function () {

    var $page = $('#invoices-index');
    var flashSuccess = $page.data('flash-success');
    var flashError = $page.data('flash-error');
    if (flashSuccess) { toastr.success(flashSuccess); }
    if (flashError) { toastr.error(flashError); }

    // ── Filters modal ────────────────────────────────────────────────────────
    $('#applyFiltersBtn').on('click', function () {
        $('#filterCondition').val($('#modalCondition').val());
        $('#filterMethods').val($('#modalMethods').val());
        $('#filters-modal').modal('hide');
        $('#searchForm').submit();
    });

});
