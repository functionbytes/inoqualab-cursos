$(function () {

    var $page = $('#invoices-index');
    var flashSuccess = $page.data('flash-success');
    var flashError = $page.data('flash-error');
    if (flashSuccess) { toastr.success(flashSuccess); }
    if (flashError) { toastr.error(flashError); }

    // ── Filters modal ────────────────────────────────────────────────────────
    function initInvoicesInvoicesTable() {
        FilterToolbar.init({
        fields: { filterCondition: 'popover_Condition', filterMethods: 'popover_Methods' },
    });
    }

    initInvoicesInvoicesTable();

    AjaxTable.init({ onLoaded: initInvoicesInvoicesTable });

});
