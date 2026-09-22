$(function () {

    var $page = $('#exams-results-index');
    var flashSuccess = $page.data('flash-success');
    var flashError = $page.data('flash-error');
    if (flashSuccess) { toastr.success(flashSuccess); }
    if (flashError) { toastr.error(flashError); }

    // ── Filters modal ────────────────────────────────────────────────────────
    function initExamsResultsTable() {
        FilterToolbar.init({
        fields: { filterCourse: 'popover_Course' },
    });
    }

    initExamsResultsTable();

    AjaxTable.init({ onLoaded: initExamsResultsTable });

});
