$(function () {
    var $page = $('#newsletter-lists-page');
    var flashSuccess = $page.data('flash-success');
    if (flashSuccess) {
        toastr.success(flashSuccess);
    }

    function initNewsletterListsTable() {
        BulkActions.init({
        url: $page.data('bulk-url'),
        entityLabel: 'lista(s)',
    });
    }

    initNewsletterListsTable();

    AjaxTable.init({ onLoaded: initNewsletterListsTable });

    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $('#delete-modal .modal-title').text($btn.data('title'));
        $('#delete-form').attr('action', $btn.data('url'));
        $('#delete-modal').modal('show');
    });
});
