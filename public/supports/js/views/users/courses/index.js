$(function () {
    var $container = $('.searchable-container');

    function initUserCoursesTable() {
        BulkActions.init({
            url: $container.data('bulkUrl'),
            entityLabel: $container.data('bulkEntityLabel'),
        });
    }

    initUserCoursesTable();

    AjaxTable.init({ onLoaded: initUserCoursesTable });
});
