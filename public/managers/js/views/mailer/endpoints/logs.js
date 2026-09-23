$(document).ready(function () {
    // El popover de filtros vive dentro de #ajax-table-root y se recrea en
    // cada carga AJAX (buscar/filtrar/paginar), asi que se re-inicializa en
    // cada AjaxTable.init({ onLoaded }).
    function initLogsTable() {
        FilterToolbar.init({
            fields: { filterStatus: 'popover_Status', filterPeriod: 'popover_Period' },
        });
    }

    initLogsTable();
    AjaxTable.init({ onLoaded: initLogsTable });
});
