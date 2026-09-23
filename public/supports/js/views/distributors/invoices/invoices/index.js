$(function () {
    var $container = $('.searchable-container');

    var flashSuccess = $container.data('flash-success');
    if (flashSuccess) {
        toastr.success(flashSuccess);
    }

    function initInvoicesTable() {
        FilterToolbar.init({
            fields: {
                filterCondition: 'popover_Condition',
                filterMethod: 'popover_Method',
            },
        });
    }

    initInvoicesTable();

    AjaxTable.init({ onLoaded: initInvoicesTable });
});
