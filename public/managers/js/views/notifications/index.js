$(function () {
    $('#btn-mark-all').on('click', function () {
        $.ajax({
            url: $('#btn-mark-all').data('mark-all-url'),
            method: 'GET',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function () {
                toastr.success('Todas las notificaciones marcadas como leidas');
                $('.badge.bg-primary.rounded-pill').remove();
            },
            error: function () {
                toastr.error('Error al marcar las notificaciones');
            }
        });
    });

    BulkActions.init({
        url: $('#btn-mark-all').data('bulk-url'),
        entityLabel: 'notificación(es)',
    });
});
