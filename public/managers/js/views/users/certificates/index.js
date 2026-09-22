$(function () {

    var $page = $('#users-certificates-index');
    var flashSuccess = $page.data('flash-success');
    var flashError = $page.data('flash-error');
    if (flashSuccess) { toastr.success(flashSuccess); }
    if (flashError) { toastr.error(flashError); }

    // ── Filters modal ────────────────────────────────────────────────────────
    function initUsersCertificatesTable() {
        FilterToolbar.init({
        fields: { filterCourse: 'popover_Course' },
    });
    }

    initUsersCertificatesTable();

    AjaxTable.init({ onLoaded: initUsersCertificatesTable });

});
