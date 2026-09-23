$(function () {
    function initEnterprisesTable() {
        FilterToolbar.init({
            fields: { filterAvailable: 'popover_Available' },
        });

        BulkActions.init({
            url: $('#searchForm').data('bulk-url'),
            entityLabel: 'empresa(s)',
        });
    }

    initEnterprisesTable();

    AjaxTable.init({ onLoaded: initEnterprisesTable });
});
