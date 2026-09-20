$(document).ready(function () {
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({ allowClear: false, width: '100%' });
    }

    BulkActions.init({
        url: $('#bulk-config').data('bulk-url'),
        entityLabel: 'variable(s)',
    });

    $(document).on('click', '.js-delete-variable', function () {
        $('#delete-form').attr('action', $(this).data('delete-url'));
    });

    $(document).on('change', '.toggle-status', function () {
        const $toggle = $(this);
        $.ajax({
            url: $toggle.data('url'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (data) {
                if (!data.success) $toggle.prop('checked', !$toggle.prop('checked'));
            },
            error: function () {
                $toggle.prop('checked', !$toggle.prop('checked'));
                toastr.error('Error al cambiar el estado');
            }
        });
    });
});
