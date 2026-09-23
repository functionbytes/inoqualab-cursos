$(function () {
    var page = $('#contactsPage');

    var flashSuccess = page.data('flash-success');
    if (flashSuccess) { toastr.success(flashSuccess); }

    function initContactsTable() {
        FilterToolbar.init({
            fields: { filterReviewed: 'popover_reviewed' },
        });

        BulkActions.init({
            url: page.data('bulk-action-url'),
            entityLabel: 'contacto(s)',
        });
    }

    initContactsTable();

    AjaxTable.init({ onLoaded: initContactsTable });
});
