(function ($) {
    'use strict';

    // ── Modal: Curso / Paquete agregado al carrito ──────────────────────────
    (function () {
        function fmtCOP(n) { return '$ ' + Number(n).toLocaleString('es-CO') + ' COP'; }

        var $overlay = $('#addedOverlay');
        function openAdded() { $overlay.addClass('open'); $('body').css('overflow', 'hidden'); }
        function closeAdded() { $overlay.removeClass('open'); $('body').css('overflow', ''); }

        $('#addedClose, #addedKeep').on('click', closeAdded);
        $overlay.on('click', function (e) { if (e.target === this) closeAdded(); });
        $(document).on('keydown', function (e) { if (e.key === 'Escape') closeAdded(); });

        $(document).on('submit', '.form-add-to-cart', function (e) {
            e.preventDefault();

            var $form = $(this);
            // Botones del formulario + los que lo envían desde fuera con form="id"
            // (barra de compra fija del detalle de curso). Cada uno guarda SU texto:
            // antes se guardaba el del primero y al terminar se ponía en todos, y
            // "Agregar al carrito" quedaba diciendo "Comprar ahora".
            var $btn = $form.find('button[type=submit]');
            if (this.id) {
                $btn = $btn.add($('button[type=submit][form="' + this.id + '"]'));
            }
            $btn.each(function () { $(this).data('originalHtml', $(this).html()); });

            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Agregando...');

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                success: function (res) {
                    if (res.buy_now && res.redirect) {
                        window.location.href = res.redirect;
                        return;
                    }

                    // Actualizar badge del header
                    var $badge = $('.cart-count-badge');
                    if (res.cart_count > 0) {
                        if ($badge.length) {
                            $badge.text(res.cart_count);
                        } else {
                            $('.cart-btn').append('<span class="cart-count-badge">' + res.cart_count + '</span>');
                        }
                    }

                    // Llenar el modal
                    $('#addedName').text(res.item.title);
                    $('#addedType').text(res.item.type === 'bundle' ? 'Paquete de cursos' : 'Curso');
                    $('#addedUnit').text(fmtCOP(res.item.price));
                    $('#addedQty').text(res.item.qty);
                    $('#addedSubtotal').text(fmtCOP(res.item.line_total));

                    // Miniatura real del producto; si no hay imagen, se conserva el ícono genérico.
                    if (res.item.image) {
                        $('#addedThumb').html('<img src="' + res.item.image + '" alt="">');
                    } else {
                        $('#addedThumb').html('<i class="fas fa-graduation-cap"></i>');
                    }

                    // Conversión: AddToCart (si hay pixels cargados)
                    if (typeof fbq !== 'undefined') {
                        fbq('track', 'AddToCart', { value: res.item.price, currency: 'COP', content_ids: [res.item.slack], content_type: 'product' });
                    }
                    if (typeof ttq !== 'undefined') {
                        ttq.track('AddToCart', { value: res.item.price, currency: 'COP', content_id: res.item.slack });
                    }
                    if (typeof gtag !== 'undefined') {
                        gtag('event', 'add_to_cart', {
                            currency: 'COP',
                            value: res.item.price,
                            items: [{ item_id: res.item.slack, price: res.item.price }]
                        });
                    }

                    openAdded();
                },
                error: function () {
                    if (typeof toastr !== 'undefined') toastr.error('Error al agregar al carrito. Inténtalo de nuevo.');
                },
                complete: function () {
                    $btn.prop('disabled', false).each(function () { $(this).html($(this).data('originalHtml')); });
                }
            });
        });
    })();

    // ── Carrito deslizable (drawer) ──────────────────────────────────────────
    (function () {
        var $drawer = $('#cartDrawer');
        var $backdrop = $('#cartBackdrop');
        var drawerUrl = $drawer.data('drawer-url');
        var removeUrl = $drawer.data('remove-url');
        var updateQtyUrl = $drawer.data('update-qty-url');

        function updateHeaderBadge(lineCount) {
            var $badge = $('.cart-count-badge');
            if (lineCount > 0) {
                if ($badge.length) { $badge.text(lineCount); }
                else { $('.cart-btn').append('<span class="cart-count-badge">' + lineCount + '</span>'); }
            } else {
                $badge.remove();
            }
        }

        function syncCounts() {
            var totalQty = 0;
            $('#cartDrawerInner .ci-qty').each(function () { totalQty += parseInt($(this).text(), 10) || 0; });
            $('#cdhCount').text(totalQty);
            updateHeaderBadge(totalQty);
        }

        function loadDrawer() {
            return $.get(drawerUrl, function (html) {
                $('#cartDrawerInner').html(html);
                syncCounts();
            });
        }

        function openCart() {
            loadDrawer();
            $drawer.addClass('open');
            $backdrop.addClass('open');
            $('body').css('overflow', 'hidden');
        }
        function closeCart() {
            $drawer.removeClass('open');
            $backdrop.removeClass('open');
            $('body').css('overflow', '');
        }

        // Abrir desde el icono del carrito del header
        $(document).on('click', '.cart-btn, .js-cart-open', function (e) {
            e.preventDefault();
            openCart();
        });
        $(document).on('click', '.js-cart-close', closeCart);
        $backdrop.on('click', closeCart);
        $(document).on('keydown', function (e) { if (e.key === 'Escape') closeCart(); });

        // Eliminar dentro del drawer
        $(document).on('click', '.js-drawer-remove', function () {
            $.post(removeUrl, { key: $(this).data('key') }, function () {
                loadDrawer();
            });
        });

        // Cambiar cantidad dentro del drawer
        function drawerQty(key, delta) {
            var $row = $('#cartDrawerInner .cart-item[data-key="' + key + '"]');
            var current = parseInt($row.find('.ci-qty').text(), 10) || 1;
            var next = Math.max(1, Math.min(10, current + delta));
            if (next === current) return;
            $.post(updateQtyUrl, { key: key, qty: next }, function () {
                loadDrawer();
            });
        }
        $(document).on('click', '.js-drawer-plus', function () { drawerQty($(this).data('key'), 1); });
        $(document).on('click', '.js-drawer-minus', function () { drawerQty($(this).data('key'), -1); });
    })();

    // ── Volver arriba + estado de carga en "Finalizar pago" ─────────────────
    (function () {
        var $btn = $('#backToTop');
        $(window).on('scroll.btt', function () {
            if ($(this).scrollTop() > 350) $btn.addClass('visible');
            else $btn.removeClass('visible');
        });
        $btn.on('click', function () { $('html,body').animate({ scrollTop: 0 }, 380); });

        // Estado de carga en "Finalizar pago"
        $(document).on('click', '#goPayBtn', function () {
            $('#goPayText').text('Cargando...');
        });
    })();

    // ── Botones flotantes (backToTop, llamar, WhatsApp): se "estacionan" ────
    (function () {
        var $footer = $('.iq-footer-sunex');
        var $wrapper = $('.page-wrapper');
        if (!$footer.length || !$wrapper.length) return;

        var floaters = [
            { el: $('#backToTop'), bottom: 156 },
            { el: $('.atl-float-call'), bottom: 90 },
            { el: $('.atl-float-text'), bottom: 24 },
        ];
        var parked = false;

        function update() {
            var footerAbsoluteTop = $footer.offset().top;
            var viewportBottom = $(window).scrollTop() + $(window).height();

            if (viewportBottom >= footerAbsoluteTop) {
                var extra = $wrapper.outerHeight() - $footer.position().top;
                floaters.forEach(function (f) {
                    f.el.css({ position: 'absolute', bottom: (extra + f.bottom) + 'px' });
                });
                parked = true;
            } else if (parked) {
                floaters.forEach(function (f) {
                    f.el.css({ position: '', bottom: '' });
                });
                parked = false;
            }
        }

        $(window).on('scroll.floatersPark resize.floatersPark', update);
        update();
    })();

    // ── Beacon de Core Web Vitals reales ─────────────────────────────────────
    (function () {
        if (!window.PerformanceObserver || !navigator.sendBeacon) return;

        var BEACON_URL = $('.page-wrapper').data('web-vitals-beacon-url');
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        var sent = {};
        var metrics = { LCP: null, CLS: 0, FCP: null, TTFB: null, INP: null };

        function send(metric, value) {
            if (sent[metric] || value === null || value === undefined || isNaN(value)) return;
            sent[metric] = true;

            var nav = performance.getEntriesByType('navigation')[0];

            var blob = new Blob([JSON.stringify({
                _token: CSRF_TOKEN,
                metric: metric,
                value: value,
                url: location.href,
                navigation_type: (nav && nav.type) || 'navigate'
            })], { type: 'application/json' });

            navigator.sendBeacon(BEACON_URL, blob);
        }

        function flushAll() {
            send('TTFB', metrics.TTFB);
            send('FCP', metrics.FCP);
            send('LCP', metrics.LCP);
            send('CLS', metrics.CLS);
            send('INP', metrics.INP);
        }

        // TTFB: ya disponible en la carga inicial vía Navigation Timing.
        try {
            var navEntry = performance.getEntriesByType('navigation')[0];
            if (navEntry) metrics.TTFB = navEntry.responseStart;
        } catch (e) {}

        // FCP
        try {
            new PerformanceObserver(function (list) {
                list.getEntries().forEach(function (entry) {
                    if (entry.name === 'first-contentful-paint') metrics.FCP = entry.startTime;
                });
            }).observe({ type: 'paint', buffered: true });
        } catch (e) {}

        // LCP: el navegador sigue reemplazando la entrada mientras la página
        // está visible -- por diseño solo se lee metrics.LCP (el último valor)
        // al momento de enviar, nunca dentro del propio observer.
        try {
            new PerformanceObserver(function (list) {
                var entries = list.getEntries();
                var last = entries[entries.length - 1];
                if (last) metrics.LCP = last.renderTime || last.loadTime;
            }).observe({ type: 'largest-contentful-paint', buffered: true });
        } catch (e) {}

        // CLS: suma de layout shifts sin input reciente del usuario.
        // Simplificado a un total de sesión completa (la spec real usa
        // ventanas de 5s) -- suficiente para detectar páginas con shifts
        // graves, que es el uso que le da el panel (worst pages / p75).
        try {
            new PerformanceObserver(function (list) {
                list.getEntries().forEach(function (entry) {
                    if (!entry.hadRecentInput) metrics.CLS += entry.value;
                });
            }).observe({ type: 'layout-shift', buffered: true });
        } catch (e) {}

        // INP: aproximado al máximo de duración de interacción observada (la
        // spec real usa el percentil 98 sobre todas las interacciones). El
        // entryType 'event' no tiene soporte universal -- todo el bloque
        // degrada en silencio si el navegador no lo implementa.
        try {
            new PerformanceObserver(function (list) {
                list.getEntries().forEach(function (entry) {
                    if (entry.duration > (metrics.INP || 0)) metrics.INP = entry.duration;
                });
            }).observe({ type: 'event', buffered: true, durationThreshold: 40 });
        } catch (e) {}

        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'hidden') flushAll();
        });
        addEventListener('pagehide', flushAll);
    })();

    // ── Popup de newsletter (solo home, si está habilitado) ──────────────────
    var $newsletterConfig = $('#newsletter-popup-config');
    if ($newsletterConfig.length) {
        (function () {
            function getCookie(n) {
                var m = document.cookie.match('(^|;)\\s*' + n + '=([^;]*)');
                return m ? m[2] : null;
            }
            function setCookie(n, v, days) {
                var d = new Date();
                d.setTime(d.getTime() + days * 86400000);
                document.cookie = n + '=' + v + ';expires=' + d.toUTCString() + ';path=/;SameSite=Lax';
            }

            if (getCookie('newsletter_popup')) return;

            var delay = parseInt($newsletterConfig.data('delay'), 10) || 2;
            var popupUrl = $newsletterConfig.data('popup-url');
            var storeUrl = $newsletterConfig.data('store-url');

            setTimeout(function () {
                if (getCookie('newsletter_popup')) return;
                $.get(popupUrl, function (html) {
                    if (!html || !html.trim()) return;
                    $('#newsletter-popup-modal').remove();
                    $('body').append(html);
                    var $modal = $('#newsletter-popup-modal');
                    $modal.modal({ backdrop: true, keyboard: true });
                    $modal.modal('show');

                    // Cerrar con el botón X personalizado
                    $(document).on('click', '#newsletter-popup-close', function () {
                        $modal.modal('hide');
                    });

                    // Al cerrar con X o backdrop → cookie 1 día
                    $modal.on('hide.bs.modal', function () {
                        if (!getCookie('newsletter_popup')) setCookie('newsletter_popup', '1', 1);
                    });

                    // Checkbox "No mostrar" → cookie 30 días
                    $(document).on('change', '#newsletter-popup-no-show-chk', function () {
                        if ($(this).prop('checked')) {
                            setCookie('newsletter_popup', '1', 30);
                            $modal.modal('hide');
                        }
                    });

                    // Envío del formulario
                    $(document).on('submit', '#newsletter-popup-form', function (e) {
                        e.preventDefault();
                        var email = $('#newsletter-popup-email').val();
                        var name = $('#newsletter-popup-name').val();
                        $('#newsletter-popup-error').hide().text('');
                        $.ajax({
                            url: storeUrl,
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                            data: { email: email, name: name },
                            success: function () {
                                setCookie('newsletter_popup', '1', 7);
                                $('#newsletter-popup-form').hide();
                                $('#newsletter-popup-no-show-chk').closest('label').hide();
                                $('#newsletter-popup-success').show();
                                setTimeout(function () { $modal.modal('hide'); }, 2500);
                            },
                            error: function (xhr) {
                                var resp = xhr.responseJSON;
                                var msg = (resp && resp.errors && resp.errors.email)
                                    ? resp.errors.email[0]
                                    : (resp && resp.message ? resp.message : 'Ingresa un correo electrónico válido.');
                                $('#newsletter-popup-error').text(msg).show();
                            }
                        });
                    });
                });
            }, delay * 1000);
        })();
    }
})(jQuery);
