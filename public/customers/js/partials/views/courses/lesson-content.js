$(function () {
    // Los dos forms de navegación (Anterior/Siguiente) deshabilitan su botón al
    // enviarse para evitar dobles envíos con doble clic.
    $('.lv-foot').on('submit', 'form', function () {
        $(this).find('button').prop('disabled', true);
    });
});

$(function () {
    var $notes = $('.lv-notes[data-lesson-notes]');
    if ($notes.length) {
        var key = 'lesson-notes-' + $notes.data('lesson-notes');
        var $foot = $notes.closest('.lv-pane').find('.lv-notes-foot');
        var $footTxt = $foot.find('.txt');
        var defaultTxt = $foot.data('default');
        var saveTimer = null;

        try { $notes.val(localStorage.getItem(key) || ''); } catch (e) {}

        $notes.on('input', function () {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(function () {
                try { localStorage.setItem(key, $notes.val()); } catch (e) {}
                $foot.addClass('saved');
                $footTxt.text('Guardado');
                setTimeout(function () {
                    $foot.removeClass('saved');
                    $footTxt.text(defaultTxt);
                }, 1600);
            }, 500);
        });
    }
});

$(function () {
    // Además del propio fullscreen del reproductor (Vimeo/YouTube, requiere
    // tocar el video primero para revelar sus controles), este botón lo
    // dispara directo sobre el iframe -- más visible en mobile.
    $('.lp-fullscreen').on('click', function () {
        var iframe = $(this).closest('.lp-video').find('iframe')[0];
        if (!iframe) return;
        var request = iframe.requestFullscreen || iframe.webkitRequestFullscreen || iframe.mozRequestFullScreen || iframe.msRequestFullscreen;
        if (request) { request.call(iframe); }
    });
});
