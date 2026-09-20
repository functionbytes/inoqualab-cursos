$(document).ready(function () {

    var $editor = $('#robots-editor');
    var updateUrl = $editor.data('update-url');
    var resetUrl = $editor.data('reset-url');

    // Función para insertar snippet al final del textarea
    function insertSnippet(snippet) {
        var current = $editor.val().trimEnd();
        var separator = current.length > 0 ? '\n\n' : '';
        $editor.val(current + separator + snippet);
        $editor.focus();
        $editor[0].scrollTop = $editor[0].scrollHeight;
    }

    // Botones de insertar snippet
    $(document).on('click', '.btn-insert-snippet', function () {
        var raw = $(this).data('snippet');
        // data() already decodes HTML entities in some cases; ensure newlines
        var snippet = String(raw).replace(/&#10;/g, '\n');
        insertSnippet(snippet);
    });

    // Guardar robots.txt via AJAX
    $('#btn-save-robots').on('click', function () {
        var $btn = $(this).prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...'
        );

        $.ajax({
            url: updateUrl,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { robots_txt: $editor.val() },
            dataType: 'json',
            success: function (response) {
                toastr.success(response.message ?? 'robots.txt guardado correctamente');
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    var first = errors[Object.keys(errors)[0]];
                    toastr.error(first ? first[0] : 'Error de validación');
                } else {
                    toastr.error('Error al guardar el robots.txt');
                }
            },
            complete: function () {
                $btn.prop('disabled', false).html('Guardar robots.txt');
            }
        });
    });

    // Restaurar default via AJAX
    $('#btn-reset-robots').on('click', function () {
        if (!window.confirm('¿Restaurar el robots.txt al valor por defecto? Esta acción no se puede deshacer.')) {
            return;
        }

        var $btn = $(this).prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-1"></span>Restaurando...'
        );

        $.ajax({
            url: resetUrl,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            dataType: 'json',
            success: function (response) {
                if (response.content !== undefined) {
                    $editor.val(response.content);
                }
                toastr.success(response.message ?? 'robots.txt restaurado al valor por defecto');
            },
            error: function () {
                toastr.error('Error al restaurar el robots.txt');
            },
            complete: function () {
                $btn.prop('disabled', false).html('Restaurar default');
            }
        });
    });

});
