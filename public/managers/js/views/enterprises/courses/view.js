$(function () {
    // Filtros (popover) sin recargar la pagina. Los dropdowns de Bootstrap
    // se auto-inicializan por atributos data-bs-*, no requieren re-init tras
    // cada carga AJAX.
    function initTable() {
        FilterToolbar.init({
            fields: { filterCulminate: 'popover_culminate' },
        });
    }

    initTable();

    AjaxTable.init({ onLoaded: initTable });
});
