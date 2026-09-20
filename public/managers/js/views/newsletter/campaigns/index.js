(function () {
    var $page = $('#campaigns-page');

    if (typeof $.fn.select2 !== 'undefined') {
        $('#modalStatus').select2({ allowClear: false, width: '100%', dropdownParent: $('#filters-modal') });
    }

    $('#applyFiltersBtn').on('click', function () {
        $('#filterStatus').val($('#modalStatus').val());
        $('#filters-modal').modal('hide');
        $('#searchForm').submit();
    });

    BulkActions.init({
        url: $page.data('bulk-url'),
        entityLabel: 'campaña(s)',
    });

    var sendUrl = null;

    $(document).on('click', '.btn-send', function () {
        sendUrl = $(this).data('url');
        $('#sendCampaignName').text($(this).data('name'));
        new bootstrap.Modal(document.getElementById('sendModal')).show();
    });

    $('#btnConfirmSend').on('click', function () {
        if (!sendUrl) return;
        var $btn = $(this).prop('disabled', true).text('Enviando...');

        $.ajax({
            url: sendUrl,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                bootstrap.Modal.getInstance(document.getElementById('sendModal')).hide();
                toastr.success(response.message);
                setTimeout(function () { location.reload(); }, 1500);
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message || 'Error al enviar la campaña.');
                $btn.prop('disabled', false).text('Sí, enviar ahora');
            },
        });
    });

    $(document).on('click', '.btn-duplicate', function () {
        $.ajax({
            url: $(this).data('url'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                toastr.success(response.message);
                if (response.redirect) {
                    setTimeout(function () { window.location.href = response.redirect; }, 900);
                }
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message || 'Error al duplicar.');
            },
        });
    });

    $(document).on('click', '.btn-retry', function () {
        $.ajax({
            url: $(this).data('url'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                toastr.success(response.message);
                setTimeout(function () { location.reload(); }, 900);
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message || 'Error al reintentar.');
            },
        });
    });

    $(document).on('click', '.btn-delete', function () {
        if (!confirm('¿Eliminar esta campaña? La acción no se puede deshacer.')) return;
        var $row = $(this).closest('tr');

        $.ajax({
            url: $(this).data('url'),
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                toastr.success(response.message);
                $row.fadeOut(400, function () { $(this).remove(); });
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message || 'Error al eliminar.');
            },
        });
    });

    if ($page.data('has-sending')) {
        setTimeout(function () {
            if (document.querySelectorAll('.modal.show').length === 0) {
                location.reload();
            }
        }, 10000);
    }
})();
