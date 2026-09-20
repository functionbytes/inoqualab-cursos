$(document).ready(function () {

    var variablesByModuleUrl = $('#formCreate').data('variables-by-module-url');

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
    function formatCode() { MailerEditor.formatCode(editor); }
    function insertVariable(name) { MailerEditor.insertVariable(name, editor); }

    // Update Preview (local, no backend call for create)
    function updatePreview() {
        const html = editor.getValue();
        const $container = $('#previewContainerTab');
        const $iframe = $('<iframe>').css({ 'width': '100%', 'min-height': '500px', 'border': 'none', 'display': 'block', 'background': 'white' });

        $container.empty().append($iframe);
        $iframe[0].srcdoc = html;
    }

    // Load Variables based on module
    function loadVariables() {
        const module = $('#module').val();

        if (!module) {
            $('#variablesPanel').html(
                '<div class="text-center py-4 text-muted"><i class="fas fa-info-circle fs-3 mb-2 d-block"></i><p class="mb-0 small">Selecciona un módulo para ver las variables disponibles</p></div>'
            );
            return;
        }

        $('#variablesPanel').html(
            '<div class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm mb-2" role="status"><span class="visually-hidden">Cargando...</span></div><p class="mb-0 small">Cargando variables...</p></div>'
        );

        $.ajax({
            url: variablesByModuleUrl,
            type: 'GET',
            data: { module: module },
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    renderVariables(data.variables);
                } else {
                    $('#variablesPanel').html(
                        '<div class="alert alert-warning m-2"><i class="fas fa-exclamation-triangle me-2"></i>No hay variables disponibles</div>'
                    );
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

        $(document).off('click.tplCreateVar').on('click.tplCreateVar', '.variable-insert', function (e) {
            e.preventDefault();
            insertVariable($(this).data('variable-name'));
        });
    }

    // Listen to module change to update variables
    $('#module').on('change', function () {
        loadVariables();
    });

    // Update preview when switching to preview tab
    $('#preview-tab').on('shown.bs.tab', function () {
        updatePreview();
    });

    // Auto-update preview on change (only if preview tab is active)
    editor.on('change', function () {
        hasChanges = true;
        updateEditorStatus('Modificado', 'pencil', 'warning');
        clearTimeout(previewTimeout);
        previewTimeout = setTimeout(function () {
            if ($('#preview-tab').hasClass('active')) {
                updatePreview();
            }
            updateEditorStatus('Listo', 'check-circle', 'success');
        }, 2000);
    });

    // Button: Refresh Preview
    $('#btnRefreshPreviewCreate').on('click', function (e) {
        e.preventDefault();
        updatePreview();
        $('#preview-tab').tab('show');
        $(this).prop('disabled', true);
        setTimeout(() => $(this).prop('disabled', false), 1000);
    });

    // Button: Refresh Preview (toolbar button)
    $('#btnRefreshPreview').on('click', function (e) {
        e.preventDefault();
        updatePreview();
        $('#preview-tab').tab('show');
    });

    // Device view switcher
    $('#preview-panel #btnDesktopView, #preview-panel #btnMobileView').on('click', function () {
        const width = $(this).data('width');
        const $container = $('#previewContainerTab');

        $('#preview-panel .btn-group .btn').removeClass('active');
        $(this).addClass('active');

        $container.css('max-width', width);

        const msg = width === '375px' ? 'Vista móvil activada' : 'Vista desktop activada';
        toastr.info(msg, 'Vista Previa', { timeOut: 1500, progressBar: true });
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
            toastr.warning('Por favor selecciona una variable', 'Atención', { timeOut: 2000 });
            return;
        }
        insertVariable(variableName);
        $('#variableSelector').val('');
    });

    // Enter key on variable selector
    $('#variableSelector').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#btnInsertVariable').click();
        }
    });

    // Ctrl+S to save
    editor.setOption('extraKeys', {
        'Ctrl-S': function (cm) {
            $('#formCreate').submit();
        },
        'Ctrl-/': 'toggleComment'
    });

    // Sync textarea before submit
    $('#formCreate').on('submit', function () {
        $('#content').val(editor.getValue());
        toastr.info('Creando plantilla...', 'Información', { timeOut: 0, extendedTimeOut: 0 });
    });
});
