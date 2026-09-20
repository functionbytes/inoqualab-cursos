$(document).ready(function () {
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({ allowClear: false, width: '100%' });
    }

    $(document).on('click', '.js-delete-component', function () {
        $('#delete-form').attr('action', $(this).data('delete-url'));
    });

    BulkActions.init({
        url: $('#bulk-config').data('bulk-url'),
        entityLabel: 'componente(s)',
    });
});
