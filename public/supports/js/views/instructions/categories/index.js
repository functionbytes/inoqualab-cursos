$(function () {

    var bulkUrl = $('#instructions-categories-list').data('bulk-url');

    function initInstructionsCategoriesTable() {
        // ── Filtros (popover) ────────────────────────────────────────────────
        FilterToolbar.init({
            fields: { filterAvailable: 'popover_available' },
        });

        // ── Bulk selection ───────────────────────────────────────────────────
        BulkActions.init({
            url: bulkUrl,
            entityLabel: 'categoría(s)',
        });
    }

    initInstructionsCategoriesTable();

    AjaxTable.init({ onLoaded: initInstructionsCategoriesTable });

});
