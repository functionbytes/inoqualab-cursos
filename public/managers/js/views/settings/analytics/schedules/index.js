$(function () {
    var page = $('#schedulesPage');

    // ── Filters modal ───────────────────────────────────────────────────────
    $('#applyFiltersBtn').on('click', function () {
        $('#filterFrequency').val($('#modalFrequency').val());
        $('#filterFormat').val($('#modalFormat').val());
        $('#filterStatus').val($('#modalStatus').val());
        $('#filters-modal').modal('hide');
        $('#searchForm').submit();
    });

    // ── Bulk selection ──────────────────────────────────────────────────────
    BulkActions.init({
        url: page.data('bulk-action-url'),
        entityLabel: 'reporte(s)',
    });

    // ── Toggle activo/inactivo ──────────────────────────────────────────────
    $(document).on('click', '.toggle-btn', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $.post($btn.data('url'), { _token: $('meta[name="csrf-token"]').attr('content') })
            .done(function (res) {
                toastr.success(res.message || 'Estado actualizado.');
                setTimeout(function () { location.reload(); }, 800);
            })
            .fail(function () {
                toastr.error('Error al cambiar el estado.');
            });
    });

    // ── Delete modal ────────────────────────────────────────────────────────
    $(document).on('click', '.delete-btn', function (e) {
        e.preventDefault();
        $('#delete-modal-title').text($(this).data('title'));
        $('#delete-form').attr('action', $(this).data('url'));
        $('#delete-modal').modal('show');
    });
});
