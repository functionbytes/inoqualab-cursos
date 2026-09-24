$(function () {
    var $container = $('.searchable-container');

    function initUserOrdersTable() {
        BulkActions.init({
            url: $container.data('bulkUrl'),
            entityLabel: $container.data('bulkEntityLabel'),
        });

        FilterToolbar.init({
            fields: { filterCondition: 'popover_Condition' },
        });
    }

    initUserOrdersTable();

    AjaxTable.init({ onLoaded: initUserOrdersTable });
});
