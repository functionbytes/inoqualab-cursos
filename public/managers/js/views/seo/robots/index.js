$(document).ready(function () {

    var $editor = $('#robots-editor');
    var updateUrl = $editor.data('update-url');
    var resetUrl = $editor.data('reset-url');

    function escHtml(t) {
        var d = document.createElement('div');
        d.textContent = String(t || '');
        return d.innerHTML;
    }

    // ── CodeMirror ────────────────────────────────────────────────────────
    // El textarea #robots-editor queda oculto (d-none): solo guarda el valor
    // inicial/de fallback. CodeMirror monta en #robotsEditorWrapper y es la
    // fuente real del contenido via editor.getValue()/.setValue().
    var editor = CodeMirror(document.getElementById('robotsEditorWrapper'), {
        value: $editor.val(),
        lineNumbers: true,
        lineWrapping: true,
    });

    // Función para insertar snippet al final del editor
    function insertSnippet(snippet) {
        var current = editor.getValue().replace(/\s+$/, '');
        var separator = current.length > 0 ? '\n\n' : '';
        editor.setValue(current + separator + snippet);
        editor.focus();
        editor.setCursor(editor.lastLine());
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
            data: { robots_txt: editor.getValue() },
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
                $btn.prop('disabled', false).html('Guardar');
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
                    editor.setValue(response.content);
                }
                toastr.success(response.message ?? 'robots.txt restaurado al valor por defecto');
            },
            error: function () {
                toastr.error('Error al restaurar el robots.txt');
            },
            complete: function () {
                $btn.prop('disabled', false).html('Restaurar al default');
            }
        });
    });

    // ── Probar URL ────────────────────────────────────────────────────────
    // Evalua la ruta contra las reglas del grupo "User-agent: *" del
    // contenido ACTUAL del editor (sin necesidad de guardar primero).
    // Coincidencia mas larga gana (misma regla que usan los motores reales);
    // soporta comodin "*" y ancla de fin de ruta "$".
    function parseRobotsRules(text) {
        var groups = [];
        var current = null;

        text.split(/\r?\n/).forEach(function (rawLine) {
            var line = rawLine.split('#')[0].trim();
            if (!line) { return; }

            var m = line.match(/^([A-Za-z-]+)\s*:\s*(.*)$/);
            if (!m) { return; }

            var directive = m[1].toLowerCase();
            var value = m[2].trim();

            if (directive === 'user-agent') {
                if (!current || current.rules.length > 0) {
                    current = { agents: [value.toLowerCase()], rules: [] };
                    groups.push(current);
                } else {
                    current.agents.push(value.toLowerCase());
                }
            } else if (directive === 'allow' || directive === 'disallow') {
                if (!current) {
                    current = { agents: ['*'], rules: [] };
                    groups.push(current);
                }
                current.rules.push({ type: directive, path: value });
            }
        });

        return groups;
    }

    function pathToRegex(path) {
        var hasEndAnchor = path.slice(-1) === '$';
        var body = hasEndAnchor ? path.slice(0, -1) : path;
        var escaped = body.replace(/[.+^${}()|[\]\\]/g, '\\$&').replace(/\*/g, '.*');
        return new RegExp('^' + escaped + (hasEndAnchor ? '$' : ''));
    }

    function testPathAgainstRobots(text, testPath) {
        var groups = parseRobotsRules(text);
        var group = groups.filter(function (g) { return g.agents.indexOf('*') !== -1; })[0] || groups[0];

        if (!group || group.rules.length === 0) {
            return { blocked: false, matchedRule: null };
        }

        var best = null;
        group.rules.forEach(function (rule) {
            if (!rule.path) { return; }
            if (pathToRegex(rule.path).test(testPath) && (!best || rule.path.length > best.path.length)) {
                best = rule;
            }
        });

        if (!best) {
            return { blocked: false, matchedRule: null };
        }

        return { blocked: best.type === 'disallow', matchedRule: best };
    }

    function runUrlTest() {
        var raw = $('#robots-test-url').val().trim();
        var $result = $('#robots-test-result');
        if (!raw) {
            $result.html('');
            return;
        }

        var path;
        try {
            var url = raw.indexOf('://') !== -1 ? new URL(raw) : new URL(raw, window.location.origin);
            path = url.pathname + (url.search || '');
        } catch (e) {
            path = raw.charAt(0) === '/' ? raw : '/' + raw;
        }

        var result = testPathAgainstRobots(editor.getValue(), path);

        if (result.blocked) {
            $result.html('<span class="text-danger fw-semibold"><i class="fas fa-ban me-1"></i>Bloqueada</span> por <code>Disallow: ' + escHtml(result.matchedRule.path) + '</code>');
        } else if (result.matchedRule) {
            $result.html('<span class="text-success fw-semibold"><i class="fas fa-check me-1"></i>Permitida</span> por <code>Allow: ' + escHtml(result.matchedRule.path) + '</code>');
        } else {
            $result.html('<span class="text-success fw-semibold"><i class="fas fa-check me-1"></i>Permitida</span> — ninguna regla coincide');
        }
    }

    $('#btn-test-url').on('click', runUrlTest);
    $('#robots-test-url').on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            runUrlTest();
        }
    });

});
