$(document).ready(function () {

    var $form = $('#formEdit');
    var previewUrl = $form.data('preview-url');
    var variablesUrl = $form.data('variables-url');

    // Initialize Bootstrap Tooltips
    $('[data-bs-toggle="tooltip"]').each(function () {
        new bootstrap.Tooltip(this);
    });

    // Initialize Select2
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({ allowClear: false, width: '100%' });
    }

    // Initialize CodeMirror via shared utility
    var extraVars = ['ORDER_ID', 'ORDER_NUMBER', 'ORDER_TOTAL', 'ORDER_STATUS', 'ORDER_DATE', 'DOCUMENT_TYPE', 'UPLOAD_LINK', 'EXPIRATION_DATE'];
    MailerEditor.registerHintHelpers(extraVars);
    const editor = MailerEditor.initCodeMirror();
    if (!editor) return;
    MailerEditor.bindAutocomplete(editor);

    let previewTimeout;
    let hasChanges = false;

    function updateEditorStatus(s, i, c) { MailerEditor.updateEditorStatus(s, i, c); }
    function updatePreviewStatus(s) { MailerEditor.updatePreviewStatus(s); }
    function formatCode() { MailerEditor.formatCode(editor); }
    function insertVariable(name) { MailerEditor.insertVariable(name, editor); }

    // Update Preview (AJAX with layout support)
    function updatePreview() {
        updatePreviewStatus('Actualizando...');
        const currentLayoutId = $('#layout_id').val();
        const currentContent = editor.getValue();

        const params = { content: currentContent };
        if (currentLayoutId) {
            params.layout_id = currentLayoutId;
        }

        $.ajax({
            url: previewUrl,
            type: 'POST',
            data: params,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    const $container = $('#previewContainer');
                    const $iframe = $('<iframe>').css({ 'width': '100%', 'border': 'none', 'display': 'block', 'background': 'white', 'overflow': 'hidden' });

                    $container.empty().append($iframe);
                    $iframe[0].srcdoc = data.html;

                    $iframe.on('load', function () {
                        try {
                            const iframeDoc = this.contentDocument || this.contentWindow.document;
                            $(this).css('height', iframeDoc.documentElement.scrollHeight + 'px');
                        } catch (e) {
                            $(this).css('height', 'auto');
                        }
                    });

                    updatePreviewStatus('En vivo');
                }
            },
            error: function () {
                updatePreviewStatus('Error');
                $('#previewContainer').html(
                    '<div class="alert alert-danger m-3"><i class="fas fa-exclamation-circle me-2"></i>Error al cargar vista previa</div>'
                );
            }
        });
    }

    // Load Variables
    function loadVariables() {
        $.ajax({
            url: variablesUrl,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    renderVariables(data.variables);
                }
            },
            error: function () {
                $('#variablesPanel').html(
                    '<div class="alert alert-danger m-2"><i class="fas fa-exclamation-circle me-2"></i>Error al cargar variables</div>'
                );
            }
        });
    }

    // Render Variables
    function renderVariables(variableGroups) {
        let html = '<div class="row g-1 px-2">';

        $.each(variableGroups, function (groupIdx, group) {
            if (group.group === 'Cliente') return true;

            $.each(group.items, function (idx, variable) {
                html += '<div class="col-6 col-md-4">';
                html += '<div class="variable-card variable-insert" data-variable-name="' + variable.name + '" data-bs-toggle="tooltip" title="' + variable.name + '">';
                html += '<code class="variable-code">{' + variable.name + '}</code>';
                html += '</div></div>';
            });
        });

        html += '</div>';
        $('#variablesPanel').html(html);

        let selectorOptions = '<option value="">-- Selecciona una variable --</option>';
        $.each(variableGroups, function (groupIdx, group) {
            if (group.group === 'Cliente') return true;
            selectorOptions += '<optgroup label="' + group.group + '">';
            $.each(group.items, function (idx, variable) {
                selectorOptions += '<option value="' + variable.name + '">{' + variable.name + '}</option>';
            });
            selectorOptions += '</optgroup>';
        });
        $('#variableSelector').html(selectorOptions);

        $('[data-bs-toggle="tooltip"]').each(function () {
            try { new bootstrap.Tooltip(this); } catch (e) { /* ignore */ }
        });

        $(document).off('click.tplEditVar').on('click.tplEditVar', '.variable-insert', function (e) {
            e.preventDefault();
            insertVariable($(this).data('variable-name'));
        });
    }

    // Initial load
    updatePreview();
    loadVariables();

    // Auto-update preview on change
    editor.on('change', function () {
        hasChanges = true;
        updateEditorStatus('Modificado', 'pencil', 'warning');
        clearTimeout(previewTimeout);
        previewTimeout = setTimeout(function () {
            updatePreview();
            updateEditorStatus('Listo', 'check-circle', 'success');
        }, 2000);
    });

    // Update preview when layout changes
    $('#layout_id').on('change', function (e) {
        e.preventDefault();
        updatePreview();
        toastr.info('Layout actualizado en la vista previa', 'Información', {
            timeOut: 2000,
            progressBar: true
        });
    });

    // Button: Refresh Preview
    $('#btnRefreshPreviewEdit').on('click', function (e) {
        e.preventDefault();
        updatePreview();
        $(this).prop('disabled', true);
        setTimeout(() => $(this).prop('disabled', false), 1000);
    });

    // Device view switcher
    $('#preview-panel #btnDesktopViewEdit, #preview-panel #btnMobileViewEdit').on('click', function () {
        const width = $(this).data('width');
        const $container = $('#previewContainer');

        $('#preview-panel .btn-group .btn').removeClass('active');
        $(this).addClass('active');

        $container.css('max-width', width);

        const msg = width === '375px' ? 'Vista móvil activada' : 'Vista desktop activada';
        toastr.info(msg, 'Vista Previa', { timeOut: 1500, progressBar: true });
    });

    // Tab: Update preview when preview tab is shown
    $('#preview-tab').on('shown.bs.tab', function () {
        updatePreview();
    });

    // Button: Load Variables
    $('#btnLoadVariables').on('click', function (e) {
        e.preventDefault();
        loadVariables();
        toastr.info('Recargando variables...', 'Información');
    });

    // Button: Format Code
    $('#btnFormatCode').on('click', function (e) {
        e.preventDefault();
        formatCode();
    });

    // Button: Insert Variable from selector
    $('#btnInsertVariable').on('click', function (e) {
        e.preventDefault();
        const variableName = $('#variableSelector').val();
        if (!variableName) {
            toastr.warning('Por favor selecciona una variable', 'Atención');
            return;
        }
        insertVariable(variableName);
        $('#variableSelector').val('');
    });

    // Enter key on variable selector
    $('#variableSelector').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            const variableName = $(this).val();
            if (!variableName) {
                toastr.warning('Por favor selecciona una variable', 'Atención');
                return;
            }
            insertVariable(variableName);
            $(this).val('');
        }
    });

    // Ctrl+S to save
    editor.setOption('extraKeys', {
        'Ctrl-S': function (cm) {
            $form.submit();
        },
        'Ctrl-/': 'toggleComment'
    });

    // Warn on unsaved changes
    window.addEventListener('beforeunload', function (e) {
        if (hasChanges) {
            e.preventDefault();
            return '';
        }
    });

    // Sync textarea before submit
    $form.on('submit', function (e) {
        const editorContent = editor.getValue();
        $('#content').val(editorContent);

        hasChanges = false;

        const $btn = $(this).find('[type="submit"]');
        $btn.prop('disabled', true);

        toastr.info('Guardando cambios...', 'Información', {
            timeOut: 0,
            extendedTimeOut: 0
        });

        return true;
    });
});
