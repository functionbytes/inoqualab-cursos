$(function () {
    var $container = $('.searchable-container');

    function initUserOrdersTable() {
        BulkActions.init({
            url: $container.data('bulkUrl'),
            entityLabel: $container.data('bulkEntityLabel'),
        });
    }

    initUserOrdersTable();

    AjaxTable.init({ onLoaded: initUserOrdersTable });
});
