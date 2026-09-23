$(function () {
    var $container = $('.searchable-container');

    var flashSuccess = $container.data('flash-success');
    if (flashSuccess) {
        toastr.success(flashSuccess);
    }

    function initUsersTable() {
        FilterToolbar.init({
            fields: { filterRole: 'popover_Role' },
        });

        BulkActions.init({
            url: $container.data('bulkUrl'),
            entityLabel: $container.data('bulkEntityLabel'),
        });
    }

    initUsersTable();

    AjaxTable.init({ onLoaded: initUsersTable });
});
