$(function () {

    var bulkUrl = $('#instructions-list').data('bulk-url');

    function initInstructionsTable() {
        // ── Filtros (popover) ────────────────────────────────────────────────
        FilterToolbar.init({
            fields: { filterAvailable: 'popover_available' },
        });

        // ── Bulk selection ───────────────────────────────────────────────────
        BulkActions.init({
            url: bulkUrl,
            entityLabel: 'instrucción(es)',
        });
    }

    initInstructionsTable();

    AjaxTable.init({ onLoaded: initInstructionsTable });

});
