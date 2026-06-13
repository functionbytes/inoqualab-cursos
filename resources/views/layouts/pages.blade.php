<!DOCTYPE html>

<html>

<head>

   
    <meta charset="UTF-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="rating" content="RTA-5042-1996-1400-1577-RTA" />
    <link rel="icon" type="image/x-icon" href="{{ getFavicon() }}">
    @seoTags
    @yield('head')
    <!--====== Flaticon ======-->
    <link rel="stylesheet" href="{{ url('/pages/css/flaticon.min.css') }}">
    <!--====== Font Awesome ======-->
    <link rel="stylesheet" href="{{ url('/pages/css/fontawesome.min.css') }}">
    <!--====== Bootstrap ======-->
    <link rel="stylesheet" href="{{ url('/pages/css/bootstrap-4.5.3.min.css') }}">
    <link rel="stylesheet" href="{{ url('managers/libs/toastr/toastr.css') }}">
    <!--====== Magnific Popup ======-->
    <link rel="stylesheet" href="{{ url('/pages/css/magnific-popup.min.css') }}">
    <!--====== Nice Select ======-->
    <link rel="stylesheet" href="{{ url('/pages/css/nice-select.min.css') }}">
    <link rel="stylesheet" href="{{ url('/pages/css/select2.min.css') }}">
    <!--====== jQuery UI ======-->
    <link rel="stylesheet" href="{{ url('/pages/css/jquery-ui.min.css') }}">
    <!--====== Animate ======-->
    <link rel="stylesheet" href="{{ url('/pages/css/animate.min.css') }}">
    <link rel="stylesheet" href="{{ url('/pages/css/slick.min.css') }}">


    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
  

    <link rel="stylesheet" href="{{ url('/pages/css/style.css') }}?v={{ @filemtime(public_path('pages/css/style.css')) ?: '1' }}">
    <style>
        /* Footer contact items */
        .footer-contact-list { display: flex; flex-direction: column; gap: 8px; }
        .footer-contact-item {
            display: flex; align-items: center; gap: 10px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 9px 14px;
            color: #cdd8e3;
            text-decoration: none;
            font-size: 13px;
            transition: background 0.2s, border-color 0.2s;
        }
        a.footer-contact-item:hover {
            background: rgba(0,139,205,0.2);
            border-color: rgba(0,139,205,0.45);
            color: #fff;
        }
        .footer-contact-icon {
            width: 34px; height: 34px; min-width: 34px;
            border-radius: 8px;
            background: #008bcd;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; color: #fff;
        }
    </style>
    <style>.cart-drawer:not(.open) { visibility: hidden; pointer-events: none; }</style>
    @stack('css')


</head>

    <div class="">

    <div class="page-wrapper">
        @include ('pages.includes.header')

        @yield('content')

        @include ('pages.includes.footer')


    </div>

        <!--====== Bootstrap ======-->
    <script src="{{ url('pages/js/jquery-3.6.0.min.js') }}" type="text/javascript"></script>
        <!--====== Bootstrap ======-->
    <script src="{{ url('pages/js/bootstrap.min.js') }}" type="text/javascript"></script>
    <!--====== Appear Js ======-->
    <script src="{{ url('pages/js/appear.min.js') }}" type="text/javascript"></script>
    <!--====== Slick ======-->
    <script src="{{ url('pages/js/slick.min.js') }}" type="text/javascript"></script>
    <!--====== jQuery UI ======-->
    <script src="{{ url('pages/js/jquery-ui.min.js') }}" type="text/javascript"></script>
    <!--====== Isotope ======-->
    <script src="{{ url('pages/js/isotope.pkgd.min.js') }}" type="text/javascript"></script>
        <script src="{{ url('managers/libs/toastr/toastr.min.js') }}" type="text/javascript"></script>
    <!--====== Circle Progress bar ======-->
    <script src="{{ url('pages/js/circle-progress.min.js') }}" type="text/javascript"></script>
    <!--====== Images Loader ======-->
    <script src="{{ url('pages/js/imagesloaded.pkgd.min.js') }}" type="text/javascript"></script>
    <!--====== Nice Select ======-->
    <script src="{{ url('pages/js/select2.min.js') }}" type="text/javascript"></script>

    <!--====== Magnific Popup ======-->
    <script src="{{ url('pages/js/jquery.magnific-popup.min.js') }}" type="text/javascript"></script>
    <!--  WOW Animation -->
    <script src="{{ url('pages/js/wow.min.js') }}" type="text/javascript"></script>
    <!-- Custom script -->
    <script src="{{ url('pages/js/script.js') }}" type="text/javascript"></script>

    <script src="{{ url('pages/js/jquery.validate.min.js') }}" type="text/javascript"></script>

    
    <script src="https://maps.google.com/maps/api/js?sensor=false"></script>
    <script>
        function initialize() {
            var latlng = new google.maps.LatLng(-34.397, 150.644);
            var myOptions = {
                zoom: 8,
                center: latlng,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            var map = new google.maps.Map(document.getElementById("map_canvas"),
                myOptions);
        }
        google.maps.event.addDomListener(window, "load", initialize);
    </script>


        @if(setting('google_analytics_enable') === 'true' && setting('google_analytics_measurement_id'))
        {{-- Google tag (gtag.js) GA4 --}}
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ setting('google_analytics_measurement_id') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag() { dataLayer.push(arguments); }
            gtag('js', new Date());
            gtag('config', '{{ setting('google_analytics_measurement_id') }}');
        </script>
        @endif

        @if(setting('meta_pixel_id'))
        {{-- Meta Pixel --}}
        <script>
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
            n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
            document,'script','https://connect.facebook.net/en_US/fbevents.js');
            fbq('init','{{ setting('meta_pixel_id') }}');
            fbq('track','PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?id={{ setting('meta_pixel_id') }}&ev=PageView&noscript=1"/></noscript>
        @endif

        @if(setting('microsoft_clarity_id'))
        {{-- Microsoft Clarity --}}
        <script>
            (function(c,l,a,r,i,t,y){c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
            t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
            y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y)})(window,
            document,"clarity","script","{{ setting('microsoft_clarity_id') }}");
        </script>
        @endif

        @if(setting('tiktok_pixel_id'))
        {{-- TikTok Pixel --}}
        <script>
            !function(w,d,t){w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];
            ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"];
            ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};
            for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);
            ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e};
            ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";
            ttq._i=ttq._i||{};ttq._i[e]=[];ttq._i[e]._u=i;ttq._t=ttq._t||{};ttq._t[e]=+new Date;
            ttq._o=ttq._o||{};ttq._o[e]=n||{};var o=document.createElement("script");
            o.type="text/javascript";o.async=!0;o.src=i+"?sdkid="+e+"&lib="+t;
            var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};
            ttq.load('{{ setting('tiktok_pixel_id') }}');ttq.page()}(window,document,'ttq');
        </script>
        @endif

        @if(setting('linkedin_insight_tag_id'))
        {{-- LinkedIn Insight Tag --}}
        <script>
            _linkedin_partner_id="{{setting('linkedin_insight_tag_id')}}";
            window._linkedin_data_partner_ids=window._linkedin_data_partner_ids||[];
            window._linkedin_data_partner_ids.push(_linkedin_partner_id);
            (function(l){if(!l){window.lintrk=function(a,b){window.lintrk.q.push([a,b])};
            window.lintrk.q=[]}var s=document.getElementsByTagName("script")[0];
            var b=document.createElement("script");b.type="text/javascript";b.async=true;
            b.src="https://snap.licdn.com/li.lms-analytics/insight.min.js";
            s.parentNode.insertBefore(b,s)})(window.lintrk);
        </script>
        <noscript><img height="1" width="1" style="display:none;" alt=""
            src="https://px.ads.linkedin.com/collect/?pid={{ setting('linkedin_insight_tag_id') }}&fmt=gif"/></noscript>
        @endif


        <script>
  (function(d,t) {
    var BASE_URL="https://chat.inoqualab.com";
    var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
    g.src=BASE_URL+"/packs/js/sdk.js";
    g.async = true;
    s.parentNode.insertBefore(g,s);
    g.onload=function(){
      window.chatwootSDK.run({
        websiteToken: 'Sn8sdgZ4toBBXcamEKoG5rco',
        baseUrl: BASE_URL
      })
    }
  })(document,"script");
</script>



        <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

    @stack('scripts')

    {{-- Modal: Curso / Paquete agregado al carrito --}}
    <div class="added-overlay" id="addedOverlay">
        <div class="added-modal" role="dialog" aria-label="Producto añadido al carrito">
            <div class="added-head">
                <div class="added-head-title">
                    <span class="added-check"><i class="fas fa-check"></i></span>
                    Producto añadido al carrito
                </div>
                <button type="button" class="added-close" id="addedClose" aria-label="Cerrar"><i class="fas fa-times"></i></button>
            </div>
            <div class="added-body">
                <div class="added-prod">
                    <div class="added-thumb"><i class="fas fa-graduation-cap"></i></div>
                    <div>
                        <div class="added-name" id="addedName"></div>
                        <div class="added-ref" id="addedType"></div>
                    </div>
                </div>
                <div class="added-divider"></div>
                <div class="added-rows">
                    <div class="added-row"><span>Precio unitario</span><b id="addedUnit"></b></div>
                    <div class="added-row"><span>Cantidad</span><b id="addedQty"></b></div>
                </div>
                <div class="added-divider"></div>
                <div class="added-subtotal">
                    <span>Subtotal</span>
                    <b id="addedSubtotal"></b>
                </div>
                <div class="added-actions">
                    <a class="added-primary" id="addedCheckout" href="{{ route('checkout.cart') }}"><i class="fas fa-lock"></i> Realizar pedido</a>
                    <button type="button" class="added-secondary" id="addedKeep">Seguir comprando</button>
                </div>
            </div>
        </div>
    </div>

    <script>
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
            var $btn  = $form.find('button[type=submit]');
            var originalHtml = $btn.html();

            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Agregando...');

            $.ajax({
                url    : $form.attr('action'),
                method : 'POST',
                data   : $form.serialize(),
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

                    openAdded();
                },
                error: function () {
                    if (typeof toastr !== 'undefined') toastr.error('Error al agregar al carrito. Inténtalo de nuevo.');
                },
                complete: function () {
                    $btn.prop('disabled', false).html(originalHtml);
                }
            });
        });
    })();
    </script>

    {{-- Carrito deslizable (drawer) --}}
    <div class="cart-backdrop" id="cartBackdrop"></div>
    <aside class="cart-drawer" id="cartDrawer" aria-label="Carrito de compras">
        <div class="cart-drawer-head">
            <div class="cdh-title">
                Tu carrito
                <span class="cdh-count" id="cdhCount">0</span>
            </div>
            <button type="button" class="cdh-close js-cart-close" aria-label="Cerrar carrito"><i class="fas fa-times"></i></button>
        </div>
        <div class="cart-drawer-inner" id="cartDrawerInner"></div>
    </aside>

    <script>
    (function () {
        var $drawer = $('#cartDrawer');
        var $backdrop = $('#cartBackdrop');

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
            var lines = $('#cartDrawerInner .cart-item').length;
            $('#cdhCount').text(totalQty);
            updateHeaderBadge(lines);
        }

        function loadDrawer() {
            return $.get('{{ route('cart.drawer') }}', function (html) {
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
            $.post('{{ route('cart.remove') }}', { key: $(this).data('key') }, function () {
                loadDrawer();
            });
        });

        // Cambiar cantidad dentro del drawer
        function drawerQty(key, delta) {
            var $row = $('#cartDrawerInner .cart-item[data-key="' + key + '"]');
            var current = parseInt($row.find('.ci-qty').text(), 10) || 1;
            var next = Math.max(1, Math.min(10, current + delta));
            if (next === current) return;
            $.post('{{ route('cart.update-qty') }}', { key: key, qty: next }, function () {
                loadDrawer();
            });
        }
        $(document).on('click', '.js-drawer-plus', function () { drawerQty($(this).data('key'), 1); });
        $(document).on('click', '.js-drawer-minus', function () { drawerQty($(this).data('key'), -1); });
    })();
    </script>

    @if(setting('page_whatsapp'))
    <a href="https://wa.me/{{ preg_replace('/\D/', '', setting('page_whatsapp')) }}?text=Hola%2C+quiero+más+información+sobre+sus+cursos."
       target="_blank" rel="noopener" class="whatsapp-float" aria-label="Contactar por WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>
    @endif

    <button id="backToTop" aria-label="Volver arriba">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
    (function () {
        var $btn = $('#backToTop');
        $(window).on('scroll.btt', function () {
            if ($(this).scrollTop() > 350) $btn.addClass('visible');
            else $btn.removeClass('visible');
        });
        $btn.on('click', function () { $('html,body').animate({ scrollTop: 0 }, 380); });

        // Spinner en "Ir al pago"
        $(document).on('click', '#goPayBtn', function () {
            $('#goPayIcon').removeClass('fa-lock').addClass('fa-spinner fa-spin');
            $('#goPayText').text('Cargando...');
        });
    })();
    </script>

@if(setting('newsletter_enabled') != '0' && setting('newsletter_popup_enabled') != '0')
<script>
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

    var delay = parseInt('{{ setting('newsletter_popup_delay') ?: 2 }}', 10) || 2;

    setTimeout(function () {
        if (getCookie('newsletter_popup')) return;
        $.get('{{ route('newsletters.ajax-popup') }}', function (html) {
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
                var name  = $('#newsletter-popup-name').val();
                $('#newsletter-popup-error').hide().text('');
                $.ajax({
                    url: '{{ route('newsletters.store') }}',
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
</script>
@endif

</body>

</html>
