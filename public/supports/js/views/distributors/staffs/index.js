$(function () {
    var $container = $('.searchable-container');

    var flashSuccess = $container.data('flash-success');
    if (flashSuccess) {
        toastr.success(flashSuccess);
    }

    function initDistributorStaffsTable() {
        FilterToolbar.init({
            fields: { filterAvailable: 'popover_Available' },
        });

        BulkActions.init({
            url: $container.data('bulkUrl'),
            entityLabel: $container.data('bulkEntityLabel'),
        });
    }

    initDistributorStaffsTable();

    AjaxTable.init({ onLoaded: initDistributorStaffsTable });
});
