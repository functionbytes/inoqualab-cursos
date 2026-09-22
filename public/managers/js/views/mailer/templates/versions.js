$(document).ready(function () {
    // Paginacion sin recargar la pagina: .btn-restore esta delegado en
    // document, asi que no necesita re-init tras cada carga AJAX.
    AjaxTable.init({});

    let $pendingRestoreForm = null;

    $(document).on('click', '.btn-restore', function (e) {
        e.preventDefault();
        const msg = $(this).data('confirm') || '¿Restaurar esta versión?';
        $pendingRestoreForm = $(this).closest('form');
        $('#confirm-restore-message').text(msg);
        new bootstrap.Modal(document.getElementById('confirm-restore-modal')).show();
    });

    $('#confirm-restore-btn').on('click', function () {
        if ($pendingRestoreForm) {
            $pendingRestoreForm.submit();
        }
    });
});
