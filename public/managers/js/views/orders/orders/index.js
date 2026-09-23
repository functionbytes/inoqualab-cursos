$(function () {

    var $page = $('#orders-index');
    var flashSuccess = $page.data('flash-success');
    var flashError = $page.data('flash-error');
    if (flashSuccess) { toastr.success(flashSuccess); }
    if (flashError) { toastr.error(flashError); }

    // ── Filtros + selección masiva ───────────────────────────────────────────
    // Se re-ejecuta tras cada carga AJAX (buscar/filtrar/paginar) porque
    // #select-all vive dentro de #ajax-table-root y se recrea.
    function initOrdersOrdersTable() {
        FilterToolbar.init({
            fields: { filterCondition: 'popover_Condition', filterType: 'popover_Type', filterMethods: 'popover_Methods' },
        });

        BulkActions.init({
            url: $page.data('bulk-action-url'),
            entityLabel: 'orden(es)',
        });
    }

    initOrdersOrdersTable();

    AjaxTable.init({ onLoaded: initOrdersOrdersTable });

    // ── Eliminar individual vía modal ────────────────────────────────────────
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $('#delete-modal .modal-title').text($btn.data('title'));
        $('#delete-form').attr('action', $btn.data('url'));
        $('#delete-modal').modal('show');
    });

});
