$(document).ready(function () {

    var config = $('#users-emails-show').data('config') || {};

    var previewFrame = document.getElementById('previewContainer');
    previewFrame.srcdoc = config.bodyHtml || '';
    previewFrame.addEventListener('load', function () {
        try {
            var height = previewFrame.contentDocument.documentElement.scrollHeight;
            previewFrame.style.height = Math.max(height, 400) + 'px';
        } catch (e) {
            // Si el navegador bloquea el acceso al documento, se queda con min-height.
        }
    });

    $('#btnDesktopView').on('click', function () {
        $('#previewContainer').css('max-width', '100%');
        $('#btnDesktopView, #btnMobileView').removeClass('active');
        $(this).addClass('active');
    });

    $('#btnMobileView').on('click', function () {
        $('#previewContainer').css('max-width', '375px');
        $('#btnDesktopView, #btnMobileView').removeClass('active');
        $(this).addClass('active');
    });

    $('#btnPrint').on('click', function () {
        window.print();
    });
});
