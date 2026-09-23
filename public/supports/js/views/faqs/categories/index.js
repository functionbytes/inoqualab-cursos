$(function () {
    var bulkUrl = $('#faqs-categories-list').data('bulk-url');

    function initFaqsCategoriesTable() {
        FilterToolbar.init({
            fields: { filterAvailable: 'popover_Available' },
        });

        BulkActions.init({
            url: bulkUrl,
            entityLabel: 'categoría(s)',
        });
    }

    initFaqsCategoriesTable();

    AjaxTable.init({ onLoaded: initFaqsCategoriesTable });
});
