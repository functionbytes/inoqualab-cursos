$(function () {
    var $container = $('.searchable-container');

    FilterToolbar.init({
        fields: { filterAvailable: 'popover_available' },
    });

    BulkActions.init({
        url: $container.data('bulkUrl'),
        entityLabel: $container.data('bulkEntityLabel'),
    });

    // El select de items-por-pagina de pagination-footer normalmente se
    // maneja vía AjaxTable (sin recargar); esta vista aun usa submit GET
    // normal, asi que se navega directo con el query param.
    $(document).on('change', '.ajax-per-page-select', function () {
        var url = new URL(window.location.href);
        url.searchParams.set('per_page', $(this).val());
        window.location.href = url.toString();
    });
});
