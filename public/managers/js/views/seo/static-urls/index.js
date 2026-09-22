$(function () {

    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    // ── Bulk selection ────────────────────────────────────────────────────────
    function initSeoStaticUrlsTable() {
        BulkActions.init({
        url: $('#bulk-config').data('bulk-url'),
        entityLabel: 'URL(s)',
    });
    }

    initSeoStaticUrlsTable();

    AjaxTable.init({ onLoaded: initSeoStaticUrlsTable });

    // ── Toggle activo vía AJAX ────────────────────────────────────────────────
    $(document).on('click', '.toggle-active', function (e) {
        e.preventDefault();
        var url = $(this).data('url');

        $.ajax({
            url: url,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: { _method: 'PATCH' },
            success: function (res) {
                toastr.success(res.message ?? 'Estado actualizado.');
                setTimeout(function () { location.reload(); }, 600);
            },
            error: function () {
                toastr.error('Error al cambiar el estado.');
            }
        });
    });

    // ── Eliminar vía modal de confirmación ────────────────────────────────────
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $('#delete-modal .modal-title').text($btn.data('title'));
        $('#delete-form').attr('action', $btn.data('url'));
        $('#delete-modal').modal('show');
    });

    $('#delete-form').on('submit', function (e) {
        e.preventDefault();
        var url = $(this).attr('action');
        var $btn = $(this).find('[type=submit]');
        $btn.prop('disabled', true).text('Eliminando...');
        $.ajax({
            url: url,
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (res) {
                $('#delete-modal').modal('hide');
                toastr.success(res.message || 'Eliminado correctamente');
                setTimeout(function () { location.reload(); }, 800);
            },
            error: function () {
                toastr.error('Error al eliminar');
                $btn.prop('disabled', false).text('Confirmar');
            }
        });
    });

});
