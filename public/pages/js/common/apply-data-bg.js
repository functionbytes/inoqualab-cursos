$(document).ready(function () {
    $('[data-bg]').each(function () {
        var url = $(this).data('bg');
        if (url) {
            $(this).css('background-image', 'url(' + url + ')');
        }
    });
});
