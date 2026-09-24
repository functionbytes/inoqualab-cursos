$(document).ready(function () {
    // FilterToolbar y BulkActions se enlazan al HTML de #ajax-table-root, que
    // AjaxTable reemplaza en cada carga, asi que se re-inicializan en onLoaded.
    function initMailerVariablesTable() {
        FilterToolbar.init({
            fields: {
                filterModule: 'popover_module',
                filterCategory: 'popover_category',
                filterStatus: 'popover_status',
            },
        });

        BulkActions.init({
            url: $('#bulk-config').data('bulk-url'),
            entityLabel: 'variable(s)',
        });
    }

    initMailerVariablesTable();

    AjaxTable.init({ onLoaded: initMailerVariablesTable });

    $(document).on('click', '.js-delete-variable', function () {
        $('#delete-form').attr('action', $(this).data('delete-url'));
    });
});
