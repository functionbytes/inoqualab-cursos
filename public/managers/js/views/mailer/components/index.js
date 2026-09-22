$(document).ready(function () {
    // select2 y BulkActions dependen de elementos dentro de #ajax-table-root
    // (el <select id="type"> y #select-all), que se recrean en cada carga
    // AJAX (buscar/filtrar/paginar), asi que se re-inicializan en cada
    // AjaxTable.init({ onLoaded: ... }).
    function initComponentsTable() {
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2').select2({ allowClear: false, width: '100%' });
        }

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
