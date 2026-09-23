$(function () {

    var $page = $('#invoices-index');
    var flashSuccess = $page.data('flash-success');
    var flashError = $page.data('flash-error');
    if (flashSuccess) { toastr.success(flashSuccess); }
    if (flashError) { toastr.error(flashError); }

    // ── Filtros + selección masiva ───────────────────────────────────────────
    // Se re-ejecuta tras cada carga AJAX (buscar/filtrar/paginar) porque
    // #select-all vive dentro de #ajax-table-root y se recrea.
    function initInvoicesInvoicesTable() {
        FilterToolbar.init({
            fields: { filterCondition: 'popover_Condition', filterMethods: 'popover_Methods' },
        });

        BulkActions.init({
            url: $page.data('bulk-action-url'),
            entityLabel: 'factura(s)',
        });
    }

    initInvoicesInvoicesTable();

    AjaxTable.init({ onLoaded: initInvoicesInvoicesTable });

});
