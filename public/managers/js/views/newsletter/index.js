$(function () {
    var $page = $('#newsletter-page');
    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    var flashSuccess = $page.data('flash-success');
    var flashError = $page.data('flash-error');

    if (flashSuccess) { toastr.success(flashSuccess); }
    if (flashError) { toastr.error(flashError); }

    BulkActions.init({
        url: $page.data('bulk-url'),
        entityLabel: 'suscriptor(es)',
    });

    var pendingAction = null;

    $(document).on('click', '.btn-unsubscribe, .btn-resubscribe', function () {
        var id = $(this).data('id');
        var email = $(this).data('email');
        var action = $(this).hasClass('btn-unsubscribe') ? 'unsubscribe' : 'resubscribe';

        pendingAction = { action: action, id: id };

        if (action === 'unsubscribe') {
            $('#action-modal-title').text('Desuscribir suscriptor');
            $('#action-modal-body').html('Se dará de baja a <strong>' + $('<span>').text(email).html() + '</strong> del newsletter. También se eliminará de Mailjet si está configurado.');
        } else {
            $('#action-modal-title').text('Reactivar suscriptor');
            $('#action-modal-body').html('Se reactivará la suscripción de <strong>' + $('<span>').text(email).html() + '</strong>. También se añadirá a Mailjet si está configurado.');
        }

        $('#action-modal').modal('show');
    });

    $('#btn-action-confirm').on('click', function () {
        if (!pendingAction) { return; }
        $('#action-modal').modal('hide');
        var ids = pendingAction.ids ? pendingAction.ids : [pendingAction.id];
        executeBulkAction(pendingAction.action, ids, function () {});
        pendingAction = null;
    });

    $('#action-modal').on('hidden.bs.modal', function () {
        pendingAction = null;
    });

    function executeBulkAction(action, ids, onSuccess) {
        $.ajax({
            url: $page.data('bulk-url'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            contentType: 'application/json',
            data: JSON.stringify({ action: action, ids: ids }),
            success: function (response) {
                toastr.success(response.message ?? 'Acción aplicada.');
                onSuccess();
                setTimeout(function () { location.reload(); }, 800);
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? 'Error al procesar la acción.');
                $('#btn-bulk-apply').prop('disabled', false).text('Aplicar');
            }
        });
    }

    $('#add-modal').on('show.bs.modal', function () {
        $('#add-email, #add-name').val('');
        $('#add-email').removeClass('is-invalid');
        $('#add-email-error').text('');
        $('#btn-add-confirm').prop('disabled', false).text('Añadir');
    });

    $('#btn-add-confirm').on('click', function () {
        var email = $.trim($('#add-email').val());
        $('#add-email').removeClass('is-invalid');
        if (!email) {
            $('#add-email').addClass('is-invalid');
            $('#add-email-error').text('El correo electrónico es obligatorio.');
            return;
        }

        $('#btn-add-confirm').prop('disabled', true).text('Guardando...');

        $.ajax({
            url: $page.data('store-url'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            contentType: 'application/json',
            data: JSON.stringify({ email: email, name: $.trim($('#add-name').val()) || null }),
            success: function (response) {
                toastr.success(response.message);
                $('#add-modal').modal('hide');
                setTimeout(function () { location.reload(); }, 700);
            },
            error: function (xhr) {
                $('#btn-add-confirm').prop('disabled', false).text('Añadir');
                var errors = xhr.responseJSON?.errors;
                if (errors?.email) {
                    $('#add-email').addClass('is-invalid');
                    $('#add-email-error').text(errors.email[0]);
                } else {
                    toastr.error(xhr.responseJSON?.message ?? 'Error al añadir el suscriptor.');
                }
            }
        });
    });

    $('#import-modal').on('show.bs.modal', function () {
        $('#import-file').val('');
        $('#import-file-error').text('');
        $('#import-result').addClass('d-none');
        $('#btn-import-confirm').prop('disabled', false).text('Importar');
    });

    $('#btn-import-confirm').on('click', function () {
        var file = $('#import-file')[0].files[0];
        $('#import-file-error').text('');
        if (!file) {
            $('#import-file-error').text('Selecciona un archivo CSV.');
            return;
        }

        var formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('file', file);

        $('#btn-import-confirm').prop('disabled', true).text('Importando...');
        $('#import-result').addClass('d-none');

        $.ajax({
            url: $page.data('import-url'),
            method: 'POST',
            contentType: false,
            processData: false,
            data: formData,
            success: function (response) {
                $('#import-result-text').text(response.message);
                $('#import-result').removeClass('d-none');
                $('#btn-import-confirm').prop('disabled', false).text('Importar otro');
                if (response.imported > 0) {
                    toastr.success(response.message);
                    setTimeout(function () { location.reload(); }, 1500);
                }
            },
            error: function (xhr) {
                $('#btn-import-confirm').prop('disabled', false).text('Importar');
                var errors = xhr.responseJSON?.errors;
                if (errors?.file) {
                    $('#import-file-error').text(errors.file[0]);
                } else {
                    toastr.error(xhr.responseJSON?.message ?? 'Error al importar el archivo.');
                }
            }
        });
    });

    if (typeof $.fn.select2 !== 'undefined') {
        $('#modalStatus, #modalSource').select2({ allowClear: false, width: '100%', dropdownParent: $('#filters-modal') });
    }

    $('#applyFiltersBtn').on('click', function () {
        $('#filterStatus').val($('#modalStatus').val());
        $('#filterSource').val($('#modalSource').val());
        $('#filters-modal').modal('hide');
        $('#searchForm').submit();
    });

    $(document).on('click', '.btn-resend', function () {
        var url = $(this).data('url');

        $.ajax({
            url: url,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (response) {
                toastr.success(response.message ?? 'Correo reenviado.');
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? 'Error al reenviar el correo.');
            }
        });
    });

    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $('#delete-modal .modal-title').text($btn.data('title'));
        $('#delete-form').attr('action', $btn.data('url'));
        $('#delete-modal').modal('show');
    });
});
