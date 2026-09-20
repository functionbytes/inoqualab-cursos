$(function () {

    var $btnGenerateAll = $('#btn-generate-all');
    var generateUrl = $btnGenerateAll.data('generate-url');
    var bulkUrl = $btnGenerateAll.data('bulk-url');
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    // ── Helpers ─────────────────────────────────────────────────────────────

    function removeRow(type, id) {
        $('#row-' + type + '-' + id).fadeOut(300, function () {
            $(this).remove();
            if ($('#orphans-table tbody tr:visible').length === 0) {
                showEmptyState();
            }
        });
    }

    function showEmptyState() {
        $('#orphans-table').closest('.table-responsive').replaceWith(
            '<div class="text-center py-5">' +
            '<i class="fas fa-check-circle fa-3x mb-3 text-success opacity-75"></i>' +
            '<h5 class="fw-bold mb-2">Todo el contenido tiene SEO configurado</h5>' +
            '<p class="text-muted">No hay contenido sin metadatos SEO</p>' +
            '</div>'
        );
        $('#btn-generate-all').prop('disabled', true);
        $('#bulk-toolbar').addClass('d-none');
    }

    function updateBulkToolbar() {
        var checked = $('.bulk-checkbox:checked').length;
        var total = $('.bulk-checkbox').length;
        $('#bulk-count').text(checked);
        checked > 0 ? $('#bulk-toolbar').removeClass('d-none') : $('#bulk-toolbar').addClass('d-none');
        $('#select-all').prop('indeterminate', checked > 0 && checked < total);
        $('#select-all').prop('checked', checked > 0 && checked === total);
    }

    // ── Seleccion masiva ─────────────────────────────────────────────────────

    $('#select-all').on('change', function () {
        $('.bulk-checkbox').prop('checked', $(this).prop('checked'));
        updateBulkToolbar();
    });

    $(document).on('change', '.bulk-checkbox', function () {
        updateBulkToolbar();
    });

    // ── Generar individual ───────────────────────────────────────────────────

    $(document).on('click', '.generate-btn', function (e) {
        e.preventDefault();
        var $link = $(this);
        var modelClass = $link.data('model-class');
        var modelId = $link.data('model-id');
        var type = $link.data('type');

        $link.text('Generando...');

        $.ajax({
            url: generateUrl,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: { model_class: modelClass, model_id: modelId },
            success: function (response) {
                if (response.status || response.success) {
                    toastr.success(response.message ?? 'SEO generado correctamente');
                    removeRow(type, modelId);
                } else {
                    toastr.error(response.message ?? 'No se pudo generar el SEO');
                    $link.text('Generar metas');
                }
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? 'Error al generar SEO');
                $link.text('Generar metas');
            }
        });
    });

    // ── Generar seleccionados ────────────────────────────────────────────────

    $('#btn-bulk-generate').on('click', function () {
        var $checked = $('.bulk-checkbox:checked');
        if (!$checked.length) {
            toastr.warning('Selecciona al menos un elemento.');
            return;
        }

        var $btn = $(this).prop('disabled', true).text('Procesando...');
        var pending = $checked.length;
        var success = 0;

        $checked.each(function () {
            var modelClass = $(this).data('model-class');
            var modelId = $(this).val();
            var type = $(this).data('type');

            $.ajax({
                url: generateUrl,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                data: { model_class: modelClass, model_id: modelId },
                success: function (response) {
                    if (response.status || response.success) {
                        success++;
                        removeRow(type, modelId);
                    }
                },
                complete: function () {
                    pending--;
                    if (pending === 0) {
                        toastr.success(success + ' elemento(s) generados correctamente.');
                        $btn.prop('disabled', false).text('Generar seleccionados');
                        $('#bulk-toolbar').addClass('d-none');
                    }
                }
            });
        });
    });

    // ── Generar todo ─────────────────────────────────────────────────────────

    $('#btn-generate-all').on('click', function () {
        var $btn = $(this).prop('disabled', true).text('Generando...');

        $.ajax({
            url: bulkUrl,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (response) {
                toastr.success(response.message ?? 'SEO generado para todo el contenido.');
                showEmptyState();
                $btn.text('Completado');
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? 'Error al generar SEO masivo.');
                $btn.prop('disabled', false).text('Generar todo');
            }
        });
    });

});
