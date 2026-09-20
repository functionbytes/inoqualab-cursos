(function () {
    'use strict';

    var gaScript = document.querySelector('script[data-ga-measurement-id]');
    if (gaScript) {
        var measurementId = gaScript.getAttribute('data-ga-measurement-id');
        window.dataLayer = window.dataLayer || [];
        window.gtag = function () { dataLayer.push(arguments); };
        gtag('js', new Date());
        gtag('config', measurementId);
    }

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
})();
