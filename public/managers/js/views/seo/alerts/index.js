$(function () {

    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    var $bulkConfig = $('#bulk-config');

    // ── Bulk selection ───────────────────────────────────────────────────────
    BulkActions.init({
        url: $bulkConfig.data('bulk-url'),
        entityLabel: 'alerta(s)',
    });

    // ── Filters modal ────────────────────────────────────────────────────────
    $('#applyFiltersBtn').on('click', function () {
        $('#filterSeverity').val($('#modalSeverity').val());
        $('#filterStatus').val($('#modalStatus').val());
        $('#filters-modal').modal('hide');
        $('#searchForm').submit();
    });

    // ── Acknowledge single alert ─────────────────────────────────────────────
    $(document).on('click', '.acknowledge-btn', function (e) {
        e.preventDefault();
        var $item = $(this);
        var url = $item.data('url');

        $item.closest('.dropdown-menu').find('.acknowledge-btn').addClass('disabled').text('Procesando...');

        $.ajax({
            url: url,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (res) {
                toastr.success(res.message || 'Alerta marcada como revisada.');
                setTimeout(function () { location.reload(); }, 600);
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? 'Error al procesar la alerta.');
                $item.removeClass('disabled').text('Marcar como revisada');
            }
        });
    });

    // ── Acknowledge all ──────────────────────────────────────────────────────
    $('#acknowledge-all-btn').on('click', function () {
        $('#acknowledgeAllModal').modal('show');
    });

    $('#confirm-acknowledge-all').on('click', function () {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Procesando...');

        $.ajax({
            url: $bulkConfig.data('acknowledge-all-url'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (res) {
                $('#acknowledgeAllModal').modal('hide');
                toastr.success(res.message || 'Todas las alertas marcadas como revisadas.');
                setTimeout(function () { location.reload(); }, 600);
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? 'Error al procesar.');
                $btn.prop('disabled', false).text('Confirmar');
            }
        });
    });

    // ── Eliminar individual vía modal ────────────────────────────────────────
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $('#delete-modal .modal-title').text($btn.data('title'));
        $('#delete-form').attr('action', $btn.data('url'));
        $('#delete-modal').modal('show');
    });

});
