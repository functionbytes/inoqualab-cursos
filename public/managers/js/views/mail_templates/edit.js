$(document).ready(function () {

    var $form = $('#formEdit');
    var previewUrl = $form.data('preview-url');
    var testUrl = $form.data('test-url');
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    var previewTimeout;
    var hasChanges = false;

    // ── CodeMirror ────────────────────────────────────────────────────────
    var editor = CodeMirror(document.getElementById('codeEditorWrapper'), {
        value: document.getElementById('content').value,
        mode: 'htmlmixed',
        theme: 'monokai',
        lineNumbers: true,
        lineWrapping: true,
        autoCloseTags: true,
        autoCloseBrackets: true,
        matchBrackets: true,
        extraKeys: {
            'Ctrl-Space': 'autocomplete',
            'Ctrl-S': function () { submitForm(); },
            'Ctrl-/': 'toggleComment'
        }
    });

    // ── Estado del editor ─────────────────────────────────────────────────
    function setStatus(text, variant) {
        var $el = $('#editorStatus');
        $el.text(text).removeClass('bg-black bg-warning bg-success bg-danger text-dark text-white');
        if (variant === 'warning') {
            $el.addClass('bg-warning text-dark');
        } else if (variant === 'success') {
            $el.addClass('bg-success text-white');
        } else if (variant === 'danger') {
            $el.addClass('bg-danger text-white');
        } else {
            $el.addClass('bg-black text-white');
        }
    }

    // ── Vista previa AJAX ─────────────────────────────────────────────────
    function updatePreview() {
        var content = editor.getValue();
        $.ajax({
            url: previewUrl,
            type: 'POST',
            data: {
                _token: csrfToken,
                content: content
            },
            dataType: 'json',
            beforeSend: function () {
                setStatus('Cargando...', 'default');
            },
            success: function (data) {
                if (!data.success) return;
                var $container = $('#previewContainer');
                var $iframe = $('<iframe>').css({ width: '100%', border: 'none', background: '#fff' });
                $container.empty().append($iframe);
                $iframe[0].srcdoc = data.html;
                $iframe.on('load', function () {
                    try {
                        var doc = this.contentDocument || this.contentWindow.document;
                        $(this).height(doc.documentElement.scrollHeight);
                    } catch (e) {
                        $(this).height(600);
                    }
                });
                setStatus('En vivo', 'success');
            },
            error: function () {
                $('#previewContainer').html(
                    '<div class="alert alert-danger m-3"><i class="fas fa-exclamation-circle me-2"></i>Error al cargar vista previa</div>'
                );
                setStatus('Error', 'danger');
            }
        });
    }

    // ── Evento de cambio en el editor ─────────────────────────────────────
    editor.on('change', function () {
        hasChanges = true;
        setStatus('Modificado', 'warning');
        clearTimeout(previewTimeout);
        previewTimeout = setTimeout(updatePreview, 2000);
    });

    // ── Botón: Formatear HTML ─────────────────────────────────────────────
    $('#btnFormatCode').on('click', function (e) {
        e.preventDefault();
        var formatted = html_beautify(editor.getValue(), {
            indent_size: 2,
            wrap_line_length: 120,
            preserve_newlines: true,
            max_preserve_newlines: 2,
            unformatted: ['a', 'span', 'strong', 'em', 'b', 'i', 'code']
        });
        editor.setValue(formatted);
        editor.focus();
        setStatus('Formateado', 'success');
        setTimeout(function () { setStatus('Listo', 'default'); }, 1500);
    });

    // ── Botón: Actualizar vista previa ────────────────────────────────────
    $('#btnRefreshPreview').on('click', function (e) {
        e.preventDefault();
        updatePreview();
        $(this).prop('disabled', true);
        setTimeout(function () { $('#btnRefreshPreview').prop('disabled', false); }, 1000);
    });

    // ── Inserción de variables ────────────────────────────────────────────
    $(document).on('click', '.variable-insert', function (e) {
        e.preventDefault();
        var varName = $(this).data('variable-name');
        editor.replaceRange('{' + varName + '}', editor.getCursor());
        editor.focus();
    });

    // ── Toggle Desktop / Móvil ────────────────────────────────────────────
    $('#btnDesktopView, #btnMobileView').on('click', function () {
        var width = $(this).data('width');
        $('#previewContainer').css('max-width', width);
        $('#btnDesktopView, #btnMobileView').removeClass('active');
        $(this).addClass('active');
    });

    // ── Abrir tab de vista previa → actualizar ────────────────────────────
    $('#preview-tab').on('shown.bs.tab', function () {
        updatePreview();
    });

    // ── Envío del formulario ──────────────────────────────────────────────
    function submitForm() {
        document.getElementById('content').value = editor.getValue();
        hasChanges = false;
        document.getElementById('formEdit').submit();
    }

    $form.on('submit', function () {
        document.getElementById('content').value = editor.getValue();
        hasChanges = false;
        $(this).find('[type="submit"]').prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...'
        );
        return true;
    });

    // ── Advertencia al salir con cambios ──────────────────────────────────
    window.addEventListener('beforeunload', function (e) {
        if (hasChanges) {
            e.preventDefault();
            return '';
        }
    });

    // ── Enviar correo de prueba ───────────────────────────────────────────
    $('#btnSendTestEmail').on('click', function () {
        var email = $('#testEmailInput').val().trim();
        if (!email) {
            $('#testEmailInput').addClass('is-invalid').focus();
            return;
        }
        $('#testEmailInput').removeClass('is-invalid');

        var $btn = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Enviando...');
        $('#testEmailResult').addClass('d-none');

        $.ajax({
            url: testUrl,
            type: 'POST',
            data: {
                _token: csrfToken,
                test_email: email,
                content: editor.getValue(),
                subject: $('input[name="subject"]').val()
            },
            dataType: 'json',
            success: function (data) {
                $('#testEmailResult')
                    .removeClass('d-none alert-danger')
                    .addClass('alert alert-success')
                    .html('<i class="fas fa-check-circle me-2"></i>' + escHtml(data.message));
            },
            error: function (xhr) {
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error al enviar el correo';
                $('#testEmailResult')
                    .removeClass('d-none alert-success')
                    .addClass('alert alert-danger')
                    .html('<i class="fas fa-exclamation-circle me-2"></i>' + escHtml(msg));
            },
            complete: function () {
                $btn.prop('disabled', false).html('Enviar prueba');
            }
        });
    });

    $('#modalTestEmail').on('hidden.bs.modal', function () {
        $('#testEmailInput').val('').removeClass('is-invalid');
        $('#testEmailResult').addClass('d-none').removeClass('alert alert-success alert-danger').html('');
    });

});
