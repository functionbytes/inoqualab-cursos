$(function () {

    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    var $modal = $('#modalRedirect');
    var $form = $('#formRedirect');
    var bulkDestroyUrl = $('#bulk-config').data('bulk-destroy-url');

    $form.on('submit', function (e) {
        e.preventDefault();
    });

    // ── Bulk selection ──────────────────────────────────────────────────────
    // Se re-ejecuta tras cada carga AJAX (buscar/filtrar/paginar) porque el
    // checkbox #select-all vive dentro de #ajax-table-root y se recrea.
    function initRedirectsTable() {
        BulkActions.init({
            url: bulkDestroyUrl,
            applyBtn: '#bulk-apply-btn',
            entityLabel: 'redirect(s)',
        });
    }

    initRedirectsTable();
    AjaxTable.init({ onLoaded: initRedirectsTable });

    // ── Nuevo redirect — resetear modal ─────────────────────────────────────
    $('[data-bs-target="#modalRedirect"]').on('click', function () {
        resetModal();
    });

    // ── Editar redirect ─────────────────────────────────────────────────────
    $(document).on('click', '.btn-edit-redirect', function (e) {
        e.preventDefault();
        var $el = $(this);
        resetModal();
        $('#modalRedirectTitle').text('Editar redirect');
        $('#redirect-id').val($el.data('id'));
        $('#source_path').val($el.data('source'));
        $('#target_path').val($el.data('target'));
        $('#status_code').val($el.data('code'));
        $('#is_regex').prop('checked', $el.data('regex') == 1);
        $('#is_wildcard').prop('checked', $el.data('wildcard') == 1);
        $('#redirect-note').val($el.data('note'));
        $modal.modal('show');
    });

    function resetModal() {
        $('#modalRedirectTitle').text('Nuevo redirect');
        $form[0].reset();
        $('#redirect-id').val('');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }

    // ── Guardar redirect ────────────────────────────────────────────────────
    $('#btn-save-redirect').on('click', function () {
        var id = $('#redirect-id').val();
        var url = id
            ? $form.data('update-url-template').replace(':id', id)
            : $form.data('store-url');
        var method = id ? 'PUT' : 'POST';

        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        var $btn = $(this).prop('disabled', true).text('Guardando...');

        $.ajax({
            url: url,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: {
                _method: method,
                source_path: $('#source_path').val(),
                target_path: $('#target_path').val(),
                status_code: $('#status_code').val(),
                is_regex: $('#is_regex').is(':checked') ? 1 : 0,
                is_wildcard: $('#is_wildcard').is(':checked') ? 1 : 0,
                note: $('#redirect-note').val(),
            },
            success: function (response) {
                toastr.success(response.message ?? 'Redirect guardado');
                $modal.modal('hide');
                setTimeout(function () { location.reload(); }, 800);
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    $.each(xhr.responseJSON.errors, function (field, messages) {
                        $('#' + field).addClass('is-invalid')
                            .next('.invalid-feedback').text(messages[0]);
                    });
                } else {
                    toastr.error('Error al guardar el redirect');
                }
            },
            complete: function () {
                $btn.prop('disabled', false).text('Guardar redirect');
            }
        });
    });

    // ── Toggle estado ───────────────────────────────────────────────────────
    $(document).on('change', '.toggle-active', function () {
        var $toggle = $(this);
        $.ajax({
            url: $toggle.data('url'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (response) {
                toastr.success(response.message ?? 'Estado actualizado');
            },
            error: function () {
                $toggle.prop('checked', !$toggle.is(':checked'));
                toastr.error('Error al cambiar el estado');
            }
        });
    });

    // ── Eliminar individual ─────────────────────────────────────────────────
    $(document).on('click', '.btn-delete-redirect', function (e) {
        e.preventDefault();
        $('#delete-form').attr('action', $(this).data('url'));
        $('#delete-modal').modal('show');
    });

    $('#delete-form').on('submit', function (e) {
        e.preventDefault();
        const url = $(this).attr('action');
        const $btn = $(this).find('[type=submit]');
        $btn.prop('disabled', true).text('Eliminando...');
        $.ajax({
            url: url,
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (res) {
                $('#delete-modal').modal('hide');
                if (res.success) {
                    toastr.success(res.message || 'Eliminado correctamente');
                    setTimeout(() => location.reload(), 800);
                } else {
                    toastr.error(res.message || 'Error al eliminar');
                    $btn.prop('disabled', false).text('Confirmar');
                }
            },
            error: function () {
                toastr.error('Error al eliminar');
                $btn.prop('disabled', false).text('Confirmar');
            }
        });
    });

});
