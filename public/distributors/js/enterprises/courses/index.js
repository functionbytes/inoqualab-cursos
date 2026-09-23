$(function () {
    var $container = $('.searchable-container');

    BulkActions.init({
        url: $container.data('bulkUrl'),
        entityLabel: $container.data('bulkEntityLabel'),
    });

    $(document).on('change', '.ajax-per-page-select', function () {
        var url = new URL(window.location.href);
        url.searchParams.set('per_page', $(this).val());
        window.location.href = url.toString();
    });
});
