$(function () {
    var page = $('#rolesPage');

    $(document).on('click', '.delete-btn', function () {
        $('#delete-form').attr('action', $(this).data('url'));
    });

    // ── Bulk selection ───────────────────────────────────────────────────────
    BulkActions.init({
        url: page.data('bulk-action-url'),
        entityLabel: 'rol(es)',
    });

    var flashSuccess = page.data('flash-success');
    var flashError = page.data('flash-error');
    if (flashSuccess) { toastr.success(flashSuccess, 'Éxito'); }
    if (flashError) { toastr.error(flashError, 'Error'); }
});
