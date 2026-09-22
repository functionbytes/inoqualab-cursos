/**
 * AjaxTable - busqueda/filtro/paginacion sin recargar la pagina para las
 * tablas del panel (managers.includes.filter-toolbar + pagination-footer).
 *
 * Requiere en el HTML de la vista:
 *   - un contenedor que envuelva el @include del partial de la tabla:
 *       <div id="ajax-table-root">
 *           @include('managers.views.{modulo}._table', [...])
 *       </div>
 *   - dentro del partial: el <form id="searchForm"> de filter-toolbar y los
 *     links de ->links() (paginacion Bootstrap, clase .pagination)
 *
 * El controller debe devolver SOLO el partial cuando la peticion es AJAX
 * ($request->ajax(), que jQuery $.get()/$.ajax() marca por defecto), y la
 * vista completa en caso contrario:
 *
 *   $view = $request->ajax() ? 'managers.views.{modulo}._table' : 'managers.views.{modulo}.index';
 *   return view($view)->with([...]);
 *
 * Uso minimo en la vista, en @push('scripts'):
 *
 *   function initContactsTable() {
 *       FilterToolbar.init({ fields: { filterReviewed: 'popover_reviewed' } });
 *       BulkActions.init({ url: page.data('bulk-action-url'), entityLabel: 'contacto(s)' });
 *   }
 *   initContactsTable();
 *   AjaxTable.init({ onLoaded: initContactsTable });
 *
 * `onLoaded` se llama tras cada fetch para re-bindear FilterToolbar/BulkActions
 * sobre el HTML nuevo (sus binds son directos, no delegados sobre document).
 */
window.AjaxTable = (function ($) {
    'use strict';

    var LINK_SELECTOR = '.pagination a, .filter-chip, .filter-chip-clear-all, .filter-popover-clear';

    function init(options) {
        var cfg = $.extend({
            container: '#ajax-table-root',
            form: '#searchForm',
            onLoaded: function () {},
        }, options);

        var $root = $(cfg.container);
        if (!$root.length) { return; }

        function load(url, pushState) {
            $root.addClass('ajax-table-loading');

            $.get(url)
                .done(function (html) {
                    $root.html(html);
                    if (pushState !== false) {
                        window.history.pushState({ ajaxTable: true }, '', url);
                    }
                    cfg.onLoaded();
                })
                .fail(function () {
                    window.location.href = url;
                })
                .always(function () {
                    $root.removeClass('ajax-table-loading');
                });
        }

        $root.off('submit.ajaxTable', cfg.form).on('submit.ajaxTable', cfg.form, function (e) {
            e.preventDefault();
            var $form = $(this);
            load($form.attr('action') + '?' + $form.serialize());
        });

        $root.off('click.ajaxTable', LINK_SELECTOR).on('click.ajaxTable', LINK_SELECTOR, function (e) {
            var href = $(this).attr('href');
            if (!href || href === '#') { return; }
            e.preventDefault();
            load(href);
        });

        // Selector "items por pagina" (managers.includes.pagination-footer):
        // navega a la misma URL con ?per_page= actualizado y ?page=1 (un
        // cambio de tamaño de pagina invalida la pagina actual).
        $root.off('change.ajaxTable', '.ajax-per-page-select').on('change.ajaxTable', '.ajax-per-page-select', function () {
            var url = new URL(window.location.href);
            url.searchParams.set('per_page', $(this).val());
            url.searchParams.set('page', '1');
            load(url.toString());
        });

        $(window).off('popstate.ajaxTable').on('popstate.ajaxTable', function () {
            load(window.location.href, false);
        });
    }

    return { init: init };
})(jQuery);
