$(document).ready(function () {

    var $list = $('#mails-list');
    var bulkUrl = $list.data('bulk-url');
    var discardUrlTemplate = $list.data('discard-url');

    BulkActions.init({
        url: bulkUrl,
        entityLabel: 'correo(s)',
    });

    var currentSlack = null;

    $(document).on('click', '.discard-btn', function (e) {
        e.preventDefault();
        currentSlack = $(this).data('slack');
        $('#discard-modal').modal('show');
    });

    $('#discard-confirm-btn').on('click', function () {
        if (!currentSlack) return;

        var url = discardUrlTemplate.replace(':slack', currentSlack);

        $.ajax({
            url: url,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                $('#discard-modal').modal('hide');
                if (response.success) {
                    toastr.success(response.message, 'Listo', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
                    setTimeout(function () { location.reload(); }, 1000);
                } else {
                    toastr.error(response.message, 'Error', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
                }
            },
            error: function () {
                $('#discard-modal').modal('hide');
                toastr.error('Error al procesar la solicitud.', 'Error', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
            },
        });
    });

});
