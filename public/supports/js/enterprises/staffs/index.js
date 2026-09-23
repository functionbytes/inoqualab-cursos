$(function () {
    function initEnterpriseStaffsTable() {
        FilterToolbar.init({
            fields: { filterAvailable: 'popover_Available' },
        });

        BulkActions.init({
            url: $('#searchForm').data('bulk-url'),
            entityLabel: 'empleado(s)',
        });
    }

    initEnterpriseStaffsTable();

    AjaxTable.init({ onLoaded: initEnterpriseStaffsTable });
});
