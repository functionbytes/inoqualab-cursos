let editor;

$(document).ready(function () {
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({ allowClear: false, width: '100%' });
    }

    var variablesUrl = $('#formCreate').data('variables-url');

    editor = CodeMirror.fromTextArea(document.getElementById('content'), {
        mode: 'htmlmixed',
        theme: 'monokai',
        lineNumbers: true,
        autoCloseTags: true,
        autoCloseBrackets: true,
        extraKeys: {
            'Ctrl-Space': 'autocomplete',
            'Ctrl-S': function () { $('#formCreate').submit(); },
            'Ctrl-/': 'toggleComment'
        }
    });

    editor.setSize(null, 500);

    let previewTimeout;

    function updatePreview() {
        const html = editor.getValue();
        const makeIframe = (container, minHeight) => {
            const $iframe = $('<iframe>').css({ width: '100%', 'min-height': minHeight, border: 'none', display: 'block', background: 'white' });
            $(container).empty().append($iframe);
            $iframe[0].srcdoc = html;
        };
        makeIframe('#previewContainer', '400px');
        makeIframe('#previewContainerTab', '500px');
    }

    function loadVariables() {
        $.get(variablesUrl, function (data) {
            if (!data.success) return;
            let html = '<div class="d-flex flex-wrap gap-1">';
            $.each(data.variables, function (i, group) {
                $.each(group.items, function (j, variable) {
                    html += '<span class="badge bg-light text-dark border variable-insert" data-name="' + variable.name + '" title="' + (variable.description || '') + '">' + variable.name + '</span>';
                });
            });
            html += '</div>';
            $('#variablesPanel').html(html);
        }).fail(function () {
            $('#variablesPanel').html('<div class="text-danger small p-2">Error al cargar variables</div>');
        });
    }

    updatePreview();
    loadVariables();

    editor.on('change', function () {
        clearTimeout(previewTimeout);
        previewTimeout = setTimeout(updatePreview, 2000);
    });

    $(document).on('click', '.variable-insert', function () {
        editor.replaceSelection('{' + $(this).data('name') + '}');
        editor.focus();
    });

    $('#btnRefreshPreview, #btnRefreshPreviewTab').on('click', function (e) { e.preventDefault(); updatePreview(); });
    $('#btnFormatCode').on('click', function (e) {
        e.preventDefault();
        if (typeof html_beautify !== 'undefined') {
            editor.setValue(html_beautify(editor.getValue(), { indent_size: 2 }));
        }
    });
    $('#btnLoadVariables').on('click', function (e) { e.preventDefault(); loadVariables(); });
    $('#btnDesktopView').on('click', function (e) { e.preventDefault(); $('#previewContainerTab').css('width', '100%'); $(this).addClass('active'); $('#btnMobileView').removeClass('active'); });
    $('#btnMobileView').on('click', function (e) { e.preventDefault(); $('#previewContainerTab').css('width', '375px'); $(this).addClass('active'); $('#btnDesktopView').removeClass('active'); });
    $('#preview-tab').on('shown.bs.tab', updatePreview);

    $('#formCreate').on('submit', function () {
        $('#content').val(editor.getValue());
    });
});
