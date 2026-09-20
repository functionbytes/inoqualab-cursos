$(document).ready(function () {
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({ allowClear: false, width: '100%' });
    }

    $('.endpoint-progress-bar').each(function () {
        var width = Math.max(0, Math.min(100, parseFloat($(this).data('width')) || 0));
        $(this).css('width', width + '%');
    });

    $(document).on('click', '.js-delete-endpoint', function () {
        $('#delete-form').attr('action', $(this).data('delete-url'));
    });

    BulkActions.init({
        url: $('#bulk-config').data('bulk-url'),
        entityLabel: 'endpoint(s)',
    });
});
