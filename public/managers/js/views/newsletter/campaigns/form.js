$(document).ready(function () {
    var $page = $('#campaign-form-page');
    var isNew = $page.data('is-new');
    var saveUrl = $page.data('save-url');
    var saveMethod = $page.data('save-method');
    var campaignExists = $page.data('campaign-exists');
    var isDraft = $page.data('is-draft');
    var isFailed = $page.data('is-failed');
    var hasChanges = false;

    var editor = CodeMirror(document.getElementById('codeEditorWrapper'), {
        value: document.getElementById('fieldContent').value,
        mode: 'htmlmixed',
        theme: 'monokai',
        lineNumbers: true,
        lineWrapping: true,
        autoCloseTags: true,
        autoCloseBrackets: true,
        matchBrackets: true,
        extraKeys: {
            'Ctrl-S': function () { saveCampaign(); },
            'Ctrl-/': 'toggleComment'
        }
    });

    editor.on('change', function () {
        hasChanges = true;
    });

    $(document).on('click', '.variable-insert', function (e) {
        e.preventDefault();
        editor.replaceRange($(this).data('variable'), editor.getCursor());
        editor.focus();
    });

    function saveCampaign() {
        var name = $.trim($('#fieldName').val());
        var subject = $.trim($('#fieldSubject').val());
        var content = editor.getValue();

        if (!name) { toastr.warning('El nombre es obligatorio.'); return; }
        if (!subject) { toastr.warning('El asunto es obligatorio.'); return; }
        if (!content) { toastr.warning('El contenido es obligatorio.'); return; }

        var $btn = $('#btnSave').prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...'
        );

        $.ajax({
            url: saveUrl,
            method: saveMethod,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
                name: name,
                subject: subject,
                preheader: $('#fieldPreheader').val(),
                content: content,
                newsletter_list_id: $('#fieldList').val() || null,
            },
            success: function (response) {
                hasChanges = false;
                if (isNew) {
                    window.location.href = response.redirect || $page.data('index-url');
                } else {
                    toastr.success(response.message);
                    $btn.prop('disabled', false).text('Guardar cambios');
                }
            },
            error: function (xhr) {
                var errors = xhr.responseJSON?.errors;
                if (errors) {
                    toastr.error(Object.values(errors).flat().join('<br>'), 'Errores de validación');
                } else {
                    toastr.error(xhr.responseJSON?.message || 'Error al guardar.');
                }
                $btn.prop('disabled', false).text('Guardar cambios');
            },
        });
    }

    $('#btnSave').on('click', saveCampaign);

    if (campaignExists) {
        var loadPreview = function () {
            var $container = $('#previewContainer');
            $container.html('<div class="text-center py-5 text-muted"><span class="spinner-border"></span></div>');

            var $iframe = $('<iframe>').css({ width: '100%', border: 'none', background: '#fff' });
            $iframe.attr('src', $page.data('preview-url'));
            $iframe.on('load', function () {
                try {
                    var doc = this.contentDocument || this.contentWindow.document;
                    $(this).height(doc.documentElement.scrollHeight);
                } catch (e) {
                    $(this).height(600);
                }
            });
            $container.empty().append($iframe);
        };

        $('#preview-tab').on('shown.bs.tab', loadPreview);
        $('#btnRefreshPreview').on('click', loadPreview);
    }

    if (campaignExists && (isDraft || isFailed)) {
        $('#btnTestSend').on('click', function () {
            var email = $.trim($('#testEmail').val());
            if (!email) { toastr.warning('Ingresa un correo para la prueba.'); return; }

            var $btn = $(this).prop('disabled', true).text('Enviando...');

            $.ajax({
                url: $page.data('test-url'),
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: { email: email },
                success: function (response) { toastr.success(response.message); },
                error: function (xhr) { toastr.error(xhr.responseJSON?.message || 'Error al enviar prueba.'); },
                complete: function () { $btn.prop('disabled', false).text('Enviar'); },
            });
        });
    }

    if (campaignExists && isDraft) {
        $('#btnSendCampaign').on('click', function () {
            $('#sendModalCount').html('<span class="spinner-border spinner-border-sm align-middle"></span>');
            $('#btnConfirmSend').prop('disabled', true);
            new bootstrap.Modal(document.getElementById('sendModal')).show();

            $.ajax({
                url: $page.data('active-count-url'),
                method: 'GET',
                success: function (response) {
                    if (response.count === 0) {
                        bootstrap.Modal.getInstance(document.getElementById('sendModal')).hide();
                        toastr.warning('No hay suscriptores activos para enviar la campaña.');
                        return;
                    }
                    $('#sendModalCount').text(response.count.toLocaleString('es-ES'));
                    $('#btnConfirmSend').prop('disabled', false);
                },
                error: function () {
                    $('#sendModalCount').text('?');
                    $('#btnConfirmSend').prop('disabled', false);
                },
            });
        });

        $('#btnConfirmSend').on('click', function () {
            var $btn = $(this).prop('disabled', true).text('Enviando...');

            $.ajax({
                url: $page.data('send-url'),
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (response) {
                    bootstrap.Modal.getInstance(document.getElementById('sendModal')).hide();
                    toastr.success(response.message);
                    setTimeout(function () {
                        window.location.href = $page.data('index-url');
                    }, 1500);
                },
                error: function (xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Error al enviar.');
                    $btn.prop('disabled', false).text('Sí, enviar ahora');
                },
            });
        });
    }

    window.addEventListener('beforeunload', function (e) {
        if (hasChanges) {
            e.preventDefault();
            return '';
        }
    });
});
