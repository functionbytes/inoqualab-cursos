(function ($) {
    'use strict';

    // ── Google Analytics (GA4) ──────────────────────────────────────────────
    var gaScript = document.querySelector('script[data-ga-measurement-id]');
    if (gaScript) {
        var gaMeasurementId = gaScript.getAttribute('data-ga-measurement-id');
        window.dataLayer = window.dataLayer || [];
        window.gtag = function () { dataLayer.push(arguments); };
        gtag('js', new Date());
        gtag('config', gaMeasurementId);
    }

    // ── Meta Pixel ───────────────────────────────────────────────────────────
    var $metaPixelConfig = $('#meta-pixel-config');
    if ($metaPixelConfig.length) {
        !function (f, b, e, v, n, t, s) {
            if (f.fbq) return;
            n = f.fbq = function () {
                n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments);
            };
            if (!f._fbq) f._fbq = n;
            n.push = n; n.loaded = !0; n.version = '2.0'; n.queue = [];
            t = b.createElement(e); t.async = !0; t.src = v;
            s = b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t, s);
        }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');

        fbq('init', $metaPixelConfig.data('meta-pixel-id'));
        fbq('track', 'PageView');
    }

    // ── Microsoft Clarity ────────────────────────────────────────────────────
    var $msClarityConfig = $('#ms-clarity-config');
    if ($msClarityConfig.length) {
        (function (c, l, a, r, i, t, y) {
            c[a] = c[a] || function () { (c[a].q = c[a].q || []).push(arguments); };
            t = l.createElement(r); t.async = 1; t.src = 'https://www.clarity.ms/tag/' + i;
            y = l.getElementsByTagName(r)[0]; y.parentNode.insertBefore(t, y);
        })(window, document, 'clarity', 'script', $msClarityConfig.data('ms-clarity-id'));
    }

    // ── TikTok Pixel ─────────────────────────────────────────────────────────
    var $tiktokConfig = $('#tiktok-pixel-config');
    if ($tiktokConfig.length) {
        !function (w, d, t) {
            w.TiktokAnalyticsObject = t;
            var ttq = w[t] = w[t] || [];
            ttq.methods = ['page', 'track', 'identify', 'instances', 'debug', 'on', 'off', 'once', 'ready', 'alias', 'group', 'enableCookie', 'disableCookie'];
            ttq.setAndDefer = function (t, e) {
                t[e] = function () { t.push([e].concat(Array.prototype.slice.call(arguments, 0))); };
            };
            for (var i = 0; i < ttq.methods.length; i++) ttq.setAndDefer(ttq, ttq.methods[i]);
            ttq.instance = function (t) {
                for (var e = ttq._i[t] || [], n = 0; n < ttq.methods.length; n++) ttq.setAndDefer(e, ttq.methods[n]);
                return e;
            };
            ttq.load = function (e, n) {
                var i = 'https://analytics.tiktok.com/i18n/pixel/events.js';
                ttq._i = ttq._i || {}; ttq._i[e] = []; ttq._i[e]._u = i;
                ttq._t = ttq._t || {}; ttq._t[e] = +new Date();
                ttq._o = ttq._o || {}; ttq._o[e] = n || {};
                var o = document.createElement('script');
                o.type = 'text/javascript'; o.async = !0; o.src = i + '?sdkid=' + e + '&lib=' + t;
                var a = document.getElementsByTagName('script')[0];
                a.parentNode.insertBefore(o, a);
            };
            ttq.load($tiktokConfig.data('tiktok-pixel-id'));
            ttq.page();
        }(window, document, 'ttq');
    }

    // ── LinkedIn Insight Tag ─────────────────────────────────────────────────
    var $linkedinConfig = $('#linkedin-insight-config');
    if ($linkedinConfig.length) {
        window._linkedin_partner_id = $linkedinConfig.data('linkedin-insight-tag-id');
        window._linkedin_data_partner_ids = window._linkedin_data_partner_ids || [];
        window._linkedin_data_partner_ids.push(window._linkedin_partner_id);
        (function (l) {
            if (!l) {
                window.lintrk = function (a, b) { window.lintrk.q.push([a, b]); };
                window.lintrk.q = [];
            }
            var s = document.getElementsByTagName('script')[0];
            var b = document.createElement('script');
            b.type = 'text/javascript'; b.async = true;
            b.src = 'https://snap.licdn.com/li.lms-analytics/insight.min.js';
            s.parentNode.insertBefore(b, s);
        })(window.lintrk);
    }

    // ── CSRF por defecto para todas las peticiones AJAX de la página ────────
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // ── Fallback de imágenes rotas (antes onerror= inline) ──────────────────
    // El evento "error" de <img> no hace bubbling, por eso se delega con
    // addEventListener en fase de captura en vez de jQuery .on().
    document.addEventListener('error', function (e) {
        var img = e.target;
        if (!img || img.tagName !== 'IMG' || !img.classList.contains('js-img-fallback') || img.dataset.fallbackHandled) {
            return;
        }
        img.dataset.fallbackHandled = '1';
        if (img.dataset.fallbackAction === 'hide-sibling') {
            img.style.display = 'none';
            if (img.nextElementSibling) {
                img.nextElementSibling.style.display = img.dataset.fallbackSiblingDisplay || '';
            }
        } else if (img.dataset.fallbackSrc) {
            img.src = img.dataset.fallbackSrc;
        }
    }, true);
})(jQuery);
