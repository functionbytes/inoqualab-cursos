$(function () {
    function initResultsTable() {
        FilterToolbar.init({
            fields: { filterYear: 'popover_Year', filterCourse: 'popover_Course' },
        });
    }

    initResultsTable();

    AjaxTable.init({ onLoaded: initResultsTable });
});
