$(function () {
    function initCoursesTable() {
        FilterToolbar.init({
            fields: { filterYear: 'popover_year', filterCulminated: 'popover_culminated' },
        });
    }

    initCoursesTable();

    AjaxTable.init({ onLoaded: initCoursesTable });
});
