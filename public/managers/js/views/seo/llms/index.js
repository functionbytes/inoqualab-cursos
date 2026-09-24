$(document).ready(function () {

    var $editor = $('#llms-editor');
    var updateUrl = $editor.data('update-url');
    var resetUrl = $editor.data('reset-url');

    // ── CodeMirror ────────────────────────────────────────────────────────
    // El textarea #llms-editor queda oculto (d-none): solo guarda el valor
    // inicial/de fallback. CodeMirror monta en #llmsEditorWrapper y es la
    // fuente real del contenido via editor.getValue()/.setValue().
    var editor = CodeMirror(document.getElementById('llmsEditorWrapper'), {
        value: $editor.val(),
        mode: 'markdown',
        lineNumbers: true,
        lineWrapping: true,
    });

    // Guardar llms.txt via AJAX
    $('#btn-save-llms').on('click', function () {
        var $btn = $(this);
        var content = editor.getValue();

        $btn.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...'
        );

        $.ajax({
            url: updateUrl,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { llms_txt: content },
            dataType: 'json',
            success: function (response) {
                toastr.success(response.message ?? 'llms.txt guardado correctamente');
            },
            error: function (xhr) {
                var msg = 'Error al guardar';
                if (xhr.responseJSON) {
                    msg = xhr.responseJSON.message ?? msg;
                    if (xhr.responseJSON.errors) {
                        var errors = xhr.responseJSON.errors;
                        msg = Object.values(errors).flat().join('<br>');
                    }
                }
                toastr.error(msg);
            },
            complete: function () {
                $btn.prop('disabled', false).html('Guardar');
            }
        });
    });

    // Restaurar default via AJAX
    $('#btn-reset-llms').on('click', function () {
        if (!confirm('¿Restaurar el llms.txt al valor por defecto? Esta acción no se puede deshacer.')) {
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-1"></span>Restaurando...'
        );

        $.ajax({
            url: resetUrl,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            dataType: 'json',
            success: function (response) {
                toastr.success(response.message ?? 'Contenido restaurado al valor por defecto');
                if (response.content !== undefined) {
                    editor.setValue(response.content);
                } else {
                    setTimeout(function () { window.location.reload(); }, 800);
                }
            },
            error: function (xhr) {
                var msg = xhr.responseJSON ? (xhr.responseJSON.message ?? 'Error al restaurar') : 'Error al restaurar';
                toastr.error(msg);
            },
            complete: function () {
                $btn.prop('disabled', false).html('Restaurar al default');
            }
        });
    });

});
