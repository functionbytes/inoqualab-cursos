$(function () {
    var $page = $('#distributors-page');
    var flashSuccess = $page.data('flash-success');
    var flashError = $page.data('flash-error');

    if (flashSuccess) { toastr.success(flashSuccess); }
    if (flashError) { toastr.error(flashError); }

    $('#applyFiltersBtn').on('click', function () {
        $('#filterAvailable').val($('#modalAvailable').val());
        $('#filters-modal').modal('hide');
        $('#searchForm').submit();
    });

    BulkActions.init({
        url: $page.data('bulk-url'),
        entityLabel: 'distribuidor(es)',
    });

    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $('#delete-modal .modal-title').text($btn.data('title'));
        $('#delete-form').attr('action', $btn.data('url'));
        $('#delete-modal').modal('show');
    });
});
