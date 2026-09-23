$(document).ready(function () {
    $('.endpoint-progress-bar').each(function () {
        var width = Math.max(0, Math.min(100, parseFloat($(this).data('width')) || 0));
        $(this).css('width', width + '%');
    });

    $(document).on('click', '.js-delete-endpoint', function () {
        $('#delete-form').attr('action', $(this).data('delete-url'));
    });

    function initMailerEndpointsTable() {
        BulkActions.init({
            url: $('#bulk-config').data('bulk-url'),
            entityLabel: 'endpoint(s)',
        });
        FilterToolbar.init({
            fields: { filterSource: 'popover_Source', filterStatus: 'popover_Status' },
        });
    }

    initMailerEndpointsTable();

    AjaxTable.init({ onLoaded: initMailerEndpointsTable });
});
