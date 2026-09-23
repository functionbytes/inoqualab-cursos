$(function () {
    var page = $('#documentsPage');

    var flashSuccess = page.data('flash-success');
    if (flashSuccess) { toastr.success(flashSuccess); }

    function initDocumentsTable() {
        FilterToolbar.init({
            fields: { filterAvailable: 'popover_Available' },
        });

        BulkActions.init({
            url: page.data('bulk-action-url'),
            entityLabel: 'documento(s)',
        });
    }

    initDocumentsTable();

    AjaxTable.init({ onLoaded: initDocumentsTable });
});
