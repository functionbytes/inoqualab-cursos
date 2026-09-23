$(function () {

    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    var $config = $('#audit-history-config');

    // ── Bulk selection + filtros ─────────────────────────────────────────────
    // Se re-ejecuta tras cada carga AJAX (buscar/filtrar/paginar) porque el
    // checkbox #select-all y el popover de filtros viven dentro de
    // #ajax-table-root y se recrean.
    function initHistoryTable() {
        BulkActions.init({
            url: $config.data('bulk-url'),
            entityLabel: 'auditoría(s)',
        });
        FilterToolbar.init({
            fields: { filterGrade: 'popover_Grade' },
        });
    }

    initHistoryTable();
    AjaxTable.init({ onLoaded: initHistoryTable });

    // ── Delete individual ─────────────────────────────────────────────────────
    $(document).on('click', '.btn-delete-log', function () {
        $('#delete-modal-title').text($(this).data('title'));
        $('#delete-form').attr('action', $(this).data('url'));
        $('#delete-modal').modal('show');
    });

    // ── Limpiar historial ─────────────────────────────────────────────────────
    $('#btn-clear-history').on('click', function () {
        $('#modal-clear-history').modal('show');
    });

    $('#btn-confirm-clear').on('click', function () {
        var $btn = $(this).prop('disabled', true).text('Limpiando...');

        $.ajax({
            url: $config.data('clear-url'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (res) {
                $('#modal-clear-history').modal('hide');
                toastr.success(res.message ?? 'Historial limpiado correctamente.');
                setTimeout(function () { location.reload(); }, 800);
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? 'Error al limpiar el historial.');
            },
            complete: function () {
                $btn.prop('disabled', false).text('Confirmar limpieza');
            }
        });
    });

});
