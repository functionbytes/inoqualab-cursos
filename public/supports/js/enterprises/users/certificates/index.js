$(function () {
    function initCertificatesTable() {
        FilterToolbar.init({
            fields: { filterCourse: 'popover_Course' },
        });
    }

    initCertificatesTable();

    AjaxTable.init({ onLoaded: initCertificatesTable });
});
