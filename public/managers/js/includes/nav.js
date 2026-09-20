(function () {
    'use strict';

    var $badge = $('#pending-mails-badge');
    if (!$badge.length) return;

    var url = $badge.data('poll-url');
    setInterval(function () {
        $.getJSON(url, function (r) {
            if (r.count > 0) {
                $badge.text(r.count > 99 ? '99+' : r.count).show();
            } else {
                $badge.hide();
            }
        });
    }, 60000);
}());
