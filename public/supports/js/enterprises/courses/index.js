$(function () {
    function initEnterpriseCoursesTable() {
        FilterToolbar.init({
            fields: { filterAvailable: 'popover_Available' },
        });
    }

    initEnterpriseCoursesTable();

    AjaxTable.init({ onLoaded: initEnterpriseCoursesTable });
});
