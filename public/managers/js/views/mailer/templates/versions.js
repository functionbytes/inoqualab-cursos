$(document).ready(function () {
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
