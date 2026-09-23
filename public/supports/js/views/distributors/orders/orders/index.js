$(function () {
    var $container = $('.searchable-container');

    var flashSuccess = $container.data('flash-success');
    if (flashSuccess) {
        toastr.success(flashSuccess);
    }

    function initDistributorOrdersTable() {
        FilterToolbar.init({
            fields: {
                filterCondition: 'popover_Condition',
                filterType: 'popover_Type',
                filterMethods: 'popover_Methods',
            },
        });
    }

    initDistributorOrdersTable();

    AjaxTable.init({ onLoaded: initDistributorOrdersTable });
});
