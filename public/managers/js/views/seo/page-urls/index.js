$(document).ready(function () {

    // ── Bulk: generar SEO para las páginas seleccionadas sin SEO ────────────
    var bulkGenerateUrl = $('[data-bulk-generate-url]').data('bulk-generate-url');
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    var $toolbar = $('#bulk-toolbar');
    var $applyBtn = $('#btn-bulk-apply');

    function getCheckedItems() {
        return $('.bulk-checkbox:checked:not(:disabled)').map(function () {
            return {
                model_class: $(this).data('model-class'),
                model_id: $(this).data('model-id'),
            };
        }).get();
    }

    function syncBulkState() {
        var $selectable = $('.bulk-checkbox:not(:disabled)');
        var checked = $('.bulk-checkbox:checked:not(:disabled)').length;

        $('#select-all').prop('indeterminate', checked > 0 && checked < $selectable.length);
        $('#select-all').prop('checked', $selectable.length > 0 && checked === $selectable.length);

        $('[data-bulk-count]').text(checked);
        checked > 0 ? $toolbar.removeClass('d-none') : $toolbar.addClass('d-none');
    }

    // #select-all vive dentro de #ajax-table-root y se recrea en cada carga
    // AJAX (buscar/filtrar/paginar), así que este bind se re-ejecuta vía
    // AjaxTable.init({ onLoaded: ... }) más abajo.
    function initPageUrlsTable() {
        $('#select-all').off('change.pageUrlsBulk').on('change.pageUrlsBulk', function () {
            $('.bulk-checkbox:not(:disabled)').prop('checked', this.checked);
            syncBulkState();
        });
    }

    initPageUrlsTable();
    AjaxTable.init({ onLoaded: initPageUrlsTable });

    $(document).on('change', '.bulk-checkbox', syncBulkState);

    $('#bulk-modal').on('hide.bs.modal', function () {
        $('#bulk-action-select').val('');
        $applyBtn.prop('disabled', false).text('Aplicar');
    });

    $applyBtn.on('click', function () {
        var action = $('#bulk-action-select').val();
        var items = getCheckedItems();

        if (!action) { toastr.warning('Selecciona una acción.'); return; }
        if (!items.length) { toastr.warning('Selecciona al menos una página.'); return; }

        $applyBtn.prop('disabled', true).text('Procesando...');

        $.ajax({
            url: bulkGenerateUrl,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            contentType: 'application/json',
            data: JSON.stringify({ items: items }),
            success: function (res) {
                $('#bulk-modal').modal('hide');
                toastr.success(res.message || 'Acción aplicada.');
                setTimeout(function () { window.location.reload(); }, 700);
            },
            error: function (xhr) {
                toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Error al procesar.');
                $applyBtn.prop('disabled', false).text('Aplicar');
            },
        });
    });

    $('#formRedirect').on('submit', function (e) {
        e.preventDefault();
    });

    // ── Abrir modal redirect con datos de la fila ────────────────────────────
    $(document).on('click', '.btn-create-redirect', function (e) {
        e.preventDefault();
        var url = $(this).data('url');

        var path = url;
        try {
            var parsed = new URL(url);
            path = parsed.pathname + (parsed.search || '');
        } catch (err) {
            path = url;
        }

        $('#redirect-source').val(path);
        $('#redirect-source-display').text(path);
        $('#redirect-target').val('').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('#redirect-code').val('301');

        $('#modalRedirect').modal('show');
    });

    // ── Guardar redirect ──────────────────────────────────────────────────────
    $('#btn-save-redirect').on('click', function () {
        var $btn = $(this).prop('disabled', true).text('Guardando...');

        $('#redirect-target').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        $.ajax({
            url: $('#formRedirect').attr('action'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
                source_path: $('#redirect-source').val(),
                target_path: $('#redirect-target').val(),
                status_code: $('#redirect-code').val(),
            },
            success: function (response) {
                toastr.success(response.message ?? 'Redirect creado correctamente');
                $('#modalRedirect').modal('hide');
                setTimeout(function () { window.location.reload(); }, 800);
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    $.each(xhr.responseJSON.errors, function (field, messages) {
                        var $input = $('#redirect-' + field.replace('_path', ''));
                        if ($input.length) {
                            $input.addClass('is-invalid')
                                  .next('.invalid-feedback').text(messages[0]);
                        } else {
                            toastr.error(messages[0]);
                        }
                    });
                } else {
                    toastr.error(xhr.responseJSON?.message ?? 'Error al guardar el redirect');
                }
            },
            complete: function () {
                $btn.prop('disabled', false).text('Guardar redirect');
            }
        });
    });

    // Limpiar modal al cerrarse
    $('#modalRedirect').on('hidden.bs.modal', function () {
        $('#formRedirect')[0].reset();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    });

});
