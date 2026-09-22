$(document).ready(function () {
    // select2 opera sobre los <select> del filtro, dentro de
    // #ajax-table-root, que se recrean en cada carga AJAX (buscar/filtrar/
    // paginar), asi que se re-inicializa en cada AjaxTable.init({ onLoaded }).
    function initLogsTable() {
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2').select2({ allowClear: false, width: '100%' });
        }
    }

    initLogsTable();
    AjaxTable.init({ onLoaded: initLogsTable });
});
