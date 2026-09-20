$(document).ready(function () {
    // Fallback de imagen para los fondos (resume-media y pc-media) si la URL falla.
    $('.resume-media, .pc-media').each(function () {
        var $el = $(this);
        var bg = $el.css('background-image');
        var match = bg && bg.match(/url\(["']?([^"')]+)["']?\)/);
        if (!match) { return; }
        var img = new Image();
        img.onerror = function () {
            $el.css('background-image', "url('" + $el.data('defaultThumb') + "')");
        };
        img.src = match[1];
    });
});
