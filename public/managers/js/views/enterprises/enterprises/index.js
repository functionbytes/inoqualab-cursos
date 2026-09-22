$(function () {
    var $page = $('#enterprises-page');
    var flashSuccess = $page.data('flash-success');
    var flashError = $page.data('flash-error');

    if (flashSuccess) { toastr.success(flashSuccess); }
    if (flashError) { toastr.error(flashError); }

    function initEnterprisesEnterprisesTable() {
        FilterToolbar.init({
        fields: { filterAvailable: 'popover_Available' },
    });
        BulkActions.init({
        url: $page.data('bulk-url'),
        entityLabel: 'empresa(s)',
    });
    }

    initEnterprisesEnterprisesTable();

    AjaxTable.init({ onLoaded: initEnterprisesEnterprisesTable });


    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $('#delete-modal .modal-title').text($btn.data('title'));
        $('#delete-form').attr('action', $btn.data('url'));
        $('#delete-modal').modal('show');
    });
});
