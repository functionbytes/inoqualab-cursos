$(function () {
    var $container = $('.searchable-container');

    BulkActions.init({
        url: $container.data('bulkUrl'),
        entityLabel: $container.data('bulkEntityLabel'),
    });
});
