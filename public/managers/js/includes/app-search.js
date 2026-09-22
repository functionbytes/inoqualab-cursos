document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    var trigger = document.getElementById('gs-trigger');
    var dropdown = document.getElementById('gs-dropdown');
    var input = document.getElementById('gs-input');
    var results = document.getElementById('gs-results');
    if (!trigger || !dropdown) return;

    // Índice del propio menú (rail + paneles ya renderizados en el DOM).
    // No hay endpoint de búsqueda global en el proyecto, así que esto
    // navega el menú, no busca en datos (usuarios, órdenes, etc.).
    function buildIndex() {
        var items = [];

        document.querySelectorAll('#appMenubarTabs .menu-link[href]').forEach(function (a) {
            var href = a.getAttribute('href');
            if (href.charAt(0) === '#') return;
            items.push({ label: a.getAttribute('aria-label') || a.textContent.trim(), url: href, group: 'Menú' });
        });

        document.querySelectorAll('#appMenubarTabsContent .menu-link[href]').forEach(function (a) {
            var pane = a.closest('.tab-pane');
            var trigger = pane ? document.querySelector('a[aria-controls="' + pane.id + '"]') : null;
            var label = a.querySelector('.menu-label');
            items.push({
                label: label ? label.textContent.trim() : a.textContent.trim(),
                url: a.getAttribute('href'),
                group: trigger ? trigger.getAttribute('aria-label') : '',
            });
        });

        return items;
    }

    var index = [];

    function esc(s) {
        var d = document.createElement('span');
        d.textContent = s != null ? String(s) : '';
        return d.innerHTML;
    }

    function render(list) {
        if (!list.length) {
            results.innerHTML = '<div class="gs-empty">Sin resultados</div>';
            return;
        }
        results.innerHTML = list.slice(0, 20).map(function (item) {
            return '<a href="' + esc(item.url) + '" class="gs-result-item">'
                + '<span>' + esc(item.label) + '</span>'
                + (item.group ? '<span class="gs-result-group">' + esc(item.group) + '</span>' : '')
                + '</a>';
        }).join('');
    }

    function open() {
        dropdown.classList.add('open');
        index = buildIndex();
        render(index);
        setTimeout(function () { input.focus(); }, 30);
    }

    function close() {
        dropdown.classList.remove('open');
        input.value = '';
    }

    trigger.addEventListener('click', function (e) {
        e.stopPropagation();
        dropdown.classList.contains('open') ? close() : open();
    });

    input.addEventListener('input', function () {
        var q = this.value.trim().toLowerCase();
        render(!q ? index : index.filter(function (item) {
            return item.label.toLowerCase().indexOf(q) !== -1;
        }));
    });

    document.addEventListener('click', function (e) {
        if (!dropdown.contains(e.target) && !trigger.contains(e.target)) close();
    });

    document.addEventListener('keydown', function (e) {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            dropdown.classList.contains('open') ? close() : open();
        }
        if (e.key === 'Escape') close();
    });
});
