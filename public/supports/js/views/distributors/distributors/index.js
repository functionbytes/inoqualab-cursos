$(function () {
    var $container = $('.searchable-container');

    var flashSuccess = $container.data('flash-success');
    if (flashSuccess) {
        toastr.success(flashSuccess);
    }

    function initDistributorsTable() {
        FilterToolbar.init({
            fields: { filterAvailable: 'popover_Available' },
        });

        BulkActions.init({
            url: $container.data('bulkUrl'),
            entityLabel: $container.data('bulkEntityLabel'),
        });
    }

    initDistributorsTable();

    AjaxTable.init({ onLoaded: initDistributorsTable });
});
