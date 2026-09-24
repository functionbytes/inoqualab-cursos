$(document).ready(function () {
    // FilterToolbar y BulkActions se enlazan al HTML de #ajax-table-root, que
    // AjaxTable reemplaza en cada carga (buscar/filtrar/paginar), asi que se
    // re-inicializan en cada AjaxTable.init({ onLoaded: ... }).
    function initComponentsTable() {
        FilterToolbar.init({
            fields: { filterType: 'popover_type' },
        });

        BulkActions.init({
            url: $('#bulk-config').data('bulk-url'),
            entityLabel: 'componente(s)',
        });
    }

    initComponentsTable();
    AjaxTable.init({ onLoaded: initComponentsTable });

    $(document).on('click', '.js-delete-component', function () {
        $('#delete-form').attr('action', $(this).data('delete-url'));
    });
});
