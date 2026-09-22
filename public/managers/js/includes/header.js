document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // Dock de usuario: toggle manual (no es un dropdown de Bootstrap), igual
    // que modules/Notification/public/js/notifications.js de webadmin.
    var dockWrap = document.getElementById('user-dock-wrap');
    var dockTrigger = document.getElementById('user-dock-trigger');
    var dock = document.getElementById('user-dock');

    if (dockTrigger && dock) {
        dockTrigger.addEventListener('click', function (e) {
            e.stopPropagation();
            dock.classList.toggle('open');
        });

        document.addEventListener('click', function (e) {
            if (dockWrap && !dockWrap.contains(e.target)) {
                dock.classList.remove('open');
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') dock.classList.remove('open');
        });
    }

    // Botón "x" del panel de notificaciones: cierra el dropdown de Bootstrap.
    document.addEventListener('click', function (e) {
        if (!e.target.closest('#btn-notif-close')) return;

        var toggle = document.getElementById('dropNotif');
        var instance = toggle && bootstrap.Dropdown.getInstance(toggle);
        if (instance) instance.hide();
    });

    // Marcar todas las notificaciones como leídas desde el header.
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('#markAllReadHeader');
        if (!btn) return;

        var url = document.getElementById('mc-app').dataset.notificationsMarkAllReadUrl;

        $.get(url, function () {
            var badge = document.getElementById('notification-badge');
            if (badge) badge.classList.remove('badge-pulse');

            document.querySelectorAll('.notif-panel-dd .notif-chip').forEach(function (chip) { chip.remove(); });
            document.querySelectorAll('.notif-panel-dd .notif-item').forEach(function (item) {
                item.classList.remove('unread');
                var newBadge = item.querySelector('.badge-new');
                if (newBadge) newBadge.remove();
            });
            document.querySelectorAll('.notif-panel-dd .notif-extra').forEach(function (extra) { extra.remove(); });

            btn.remove();
            toastr.success('Notificaciones marcadas como leídas', '', { timeOut: 2000 });
        });
    });
});
