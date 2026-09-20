document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // Tooltips del rail de iconos (inicializados en public/managers/js/custom.js):
    // al estar tan pegados unos a otros, el hover/click entre iconos consecutivos
    // puede dejar un tooltip "pegado" sin ocultarse. Forzamos ocultar cualquier
    // otro tooltip abierto al mostrar uno nuevo, y ocultar el propio al hacer clic.
    document.querySelectorAll('.mini-nav-item [data-bs-toggle="tooltip"]').forEach(function (trigger) {
        trigger.addEventListener('show.bs.tooltip', function () {
            document.querySelectorAll('.mini-nav-item [data-bs-toggle="tooltip"]').forEach(function (other) {
                if (other === trigger) return;
                var instance = bootstrap.Tooltip.getInstance(other);
                if (instance) instance.hide();
            });
        });

        trigger.addEventListener('click', function () {
            var instance = bootstrap.Tooltip.getInstance(trigger);
            if (instance) instance.hide();
        });
    });

    document.querySelectorAll('.mini-nav-item').forEach(function (item) {
        item.addEventListener('click', function (e) {
            var directUrl = this.dataset.directUrl;
            if (directUrl) {
                return;
            }

            e.preventDefault();

            var sidebarId = this.dataset.sidebarId;
            if (!sidebarId) {
                return;
            }

            document.querySelectorAll('.mini-nav-item').forEach(function (navItem) {
                navItem.classList.remove('selected');
            });
            this.classList.add('selected');

            document.querySelectorAll('.sidebarmenu .sidebar-nav').forEach(function (nav) {
                nav.classList.remove('d-block');
                nav.classList.add('d-none');
            });

            var targetSidebar = document.querySelector('#menu-right-' + sidebarId);
            if (targetSidebar) {
                targetSidebar.classList.remove('d-none');
                targetSidebar.classList.add('d-block');

                var sidebarmenu = document.querySelector('.sidebarmenu');
                if (sidebarmenu) sidebarmenu.classList.remove('d-none');

                var miniPanel = document.querySelector('aside.side-mini-panel');
                if (miniPanel) miniPanel.classList.add('with-vertical');
            }
        });
    });

    document.querySelectorAll('.sidebarmenu .sidebar-link.has-arrow').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();

            var isActive = this.classList.contains('active');
            var parentUl = this.closest('ul');
            var submenu = this.nextElementSibling;

            if (!isActive) {
                parentUl.querySelectorAll('ul').forEach(function (ul) { ul.classList.remove('in'); });
                parentUl.querySelectorAll('a').forEach(function (navLink) { navLink.classList.remove('active'); });

                if (submenu) submenu.classList.add('in');
                this.classList.add('active');
            } else {
                this.classList.remove('active');
                if (submenu) submenu.classList.remove('in');
            }
        });
    });
});
