$(function () {
    var page = $('#faqsPage');

    var flashSuccess = page.data('flash-success');
    if (flashSuccess) { toastr.success(flashSuccess); }

    function initFaqsTable() {
        FilterToolbar.init({
            fields: { filterAvailable: 'popover_Available' },
        });

        BulkActions.init({
            url: page.data('bulk-action-url'),
            entityLabel: 'pregunta(s)',
        });
    }

    initFaqsTable();

    AjaxTable.init({ onLoaded: initFaqsTable });
});
