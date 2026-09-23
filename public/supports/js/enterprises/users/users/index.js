$(function () {
    function initEnterpriseUsersTable() {
        FilterToolbar.init({
            fields: { filterAvailable: 'popover_Available' },
        });
    }

    initEnterpriseUsersTable();

    AjaxTable.init({ onLoaded: initEnterpriseUsersTable });
});
