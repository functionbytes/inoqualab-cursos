$(document).ready(function () {

    var MAX_URLS = 50;
    var $textarea = $('#urls-input');
    var host = $textarea.data('host') || '';

    function parseLines() {
        var raw = $textarea.val().trim();
        if (!raw) { return []; }
        return raw.split('\n')
            .map(function (u) { return u.trim(); })
            .filter(function (u) { return u.length > 0; });
    }

    function isValidUrl(value) {
        try {
            var parsed = new URL(value);
            return parsed.protocol === 'http:' || parsed.protocol === 'https:';
        } catch (e) {
            return false;
        }
    }

    function urlHost(value) {
        try {
            return new URL(value).host;
        } catch (e) {
            return null;
        }
    }

    function renderList($ul, lines, max) {
        $ul.empty();
        lines.slice(0, max).forEach(function (line) {
            $ul.append($('<li></li>').text(line));
        });
        if (lines.length > max) {
            $ul.append($('<li></li>').text('+ ' + (lines.length - max) + ' más'));
        }
    }

    function validateUrls() {
        var lines = parseLines();
        var invalidFormat = [];
        var wrongHost = [];

        lines.forEach(function (line) {
            if (!isValidUrl(line)) {
                invalidFormat.push(line);
                return;
            }
            if (host && urlHost(line) !== host) {
                wrongHost.push(line);
            }
        });

        var overLimit = lines.length > MAX_URLS;

        // Contador
        var $counter = $('#urls-counter');
        $counter.text(lines.length + ' / ' + MAX_URLS);
        $counter.toggleClass('indexnow-counter--over', overLimit);

        // Bloqueantes: formato inválido o exceso del límite
        var $blocking = $('#urls-validation-blocking');
        if (invalidFormat.length || overLimit) {
            var title = overLimit
                ? 'Máximo ' + MAX_URLS + ' URLs por envío (tienes ' + lines.length + ').'
                : invalidFormat.length + ' línea(s) no son URLs válidas:';
            $('#urls-validation-blocking-title').text(title);
            renderList($('#urls-validation-blocking-list'), invalidFormat, 5);
            $('#urls-validation-blocking-list').toggleClass('d-none', invalidFormat.length === 0);
            $blocking.removeClass('d-none');
        } else {
            $blocking.addClass('d-none');
        }

        // Aviso (no bloquea el envío): host distinto al configurado
        var $warning = $('#urls-validation-warning');
        if (host && wrongHost.length) {
            $('#urls-validation-warning-title').text(wrongHost.length + ' URL(s) no coinciden con el host configurado (' + host + '):');
            renderList($('#urls-validation-warning-list'), wrongHost, 5);
            $warning.removeClass('d-none');
        } else {
            $warning.addClass('d-none');
        }

        $('#urls-validation').toggleClass('d-none', invalidFormat.length === 0 && !overLimit && wrongHost.length === 0);

        var hasBlockingIssue = invalidFormat.length > 0 || overLimit || lines.length === 0;
        $('#btn-submit-indexnow').prop('disabled', hasBlockingIssue || $textarea.prop('disabled'));
    }

    if ($textarea.length && !$textarea.prop('disabled')) {
        $('#btn-submit-indexnow').prop('disabled', true);
        $textarea.on('input', validateUrls);
    }

    $('#btn-submit-indexnow').on('click', function () {
        var rawText = $('#urls-input').val().trim();

        if (!rawText) {
            toastr.warning('Escribe al menos una URL antes de enviar.', 'Aviso', {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-bottom-right'
            });
            return;
        }

        var urls = rawText.split('\n')
            .map(function (u) { return u.trim(); })
            .filter(function (u) { return u.length > 0; });

        if (!urls.length) {
            toastr.warning('No se encontraron URLs válidas.', 'Aviso', {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-bottom-right'
            });
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Enviando...');

        $.ajax({
            url: $btn.data('submit-url'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { urls: urls },
            success: function (response) {
                toastr.success(response.message ?? 'URLs enviadas correctamente.', 'Exito', {
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-bottom-right'
                });
                $('#urls-input').val('');
            },
            error: function (xhr) {
                var message = 'Error al enviar las URLs.';
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    var firstKey = Object.keys(errors)[0];
                    message = errors[firstKey][0];
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                toastr.error(message, 'Error', {
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-bottom-right'
                });
            },
            complete: function () {
                $btn.prop('disabled', false).html('Enviar a IndexNow');
            }
        });
    });

});
