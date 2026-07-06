@extends('layouts.pages')

@section('title', 'Facturación')

@section('content')

<main class="cartx">

    {{-- Band / breadcrumb --}}
    <div class="cartx-band">
        <div class="container">
            <div class="cartx-crumb">
                INICIO <span class="sep">/</span> <span class="cur">FACTURACIÓN</span>
            </div>
            <h1>Facturación</h1>
            <p class="lede">Completa tus datos para finalizar la inscripción a tus cursos.</p>
        </div>
    </div>

    <section class="cartx-checkout">
        <div class="container">
            <div class="cartx-grid">

                {{-- ===== Columna izquierda: formulario ===== --}}
                <div class="cartx-reveal">
                    <form id="formCheckouts" class="form-card" enctype="multipart/form-data" role="form" onSubmit="return false">
                        @csrf

                        <input type="hidden" id="coupon" name="coupon" value="{{ $coupon }}">
                        <input type="hidden" id="subtotal" name="subtotal" value="{{ $subtotal }}">
                        <input type="hidden" id="discount" name="discount" value="{{ $discount }}">
                        <input type="hidden" id="total" name="total" value="{{ $total }}">
                        <input type="hidden" name="auth" value="{{ Auth::check() ? 'auth' : 'unauth' }}">

                        <h2>Detalles de facturación</h2>
                        <p class="sub">Mantener tus datos de facturación actualizados es fundamental para recibir tu certificado y facilitar el proceso de pago. Si necesitas ayuda, contáctanos por WhatsApp.</p>

                        <div class="field-grid">

                            <div class="field">
                                <label for="firstname">Nombres <span class="req">*</span></label>
                                <input type="text" id="firstname" name="firstname" class="control" autocomplete="given-name" placeholder="Ej. María Fernanda" value="{{ Auth::check() ? $user->firstname : '' }}">
                                <label id="firstname-error" class="error" for="firstname"></label>
                            </div>

                            <div class="field">
                                <label for="lastname">Apellidos <span class="req">*</span></label>
                                <input type="text" id="lastname" name="lastname" class="control" autocomplete="family-name" placeholder="Ej. Gómez Ríos" value="{{ Auth::check() ? $user->lastname : '' }}">
                                <label id="lastname-error" class="error" for="lastname"></label>
                            </div>

                            <div class="field">
                                <label for="identification_type">Tipo de documento <span class="req">*</span></label>
                                @php $idt = Auth::check() ? ($user->identification_type ?: 'CC') : 'CC'; @endphp
                                <select id="identification_type" name="identification_type" class="select2">
                                    <option value="CC" @selected($idt=='CC')>CC · Cédula de ciudadanía</option>
                                    <option value="CE" @selected($idt=='CE')>CE · Cédula de extranjería</option>
                                    <option value="TI" @selected($idt=='TI')>TI · Tarjeta de identidad</option>
                                    <option value="NIT" @selected($idt=='NIT')>NIT</option>
                                    <option value="PAS" @selected($idt=='PAS')>PAS · Pasaporte</option>
                                    <option value="PEP" @selected($idt=='PEP')>PEP · Permiso especial</option>
                                </select>
                                <label id="identification_type-error" class="error" for="identification_type"></label>
                            </div>

                            <div class="field">
                                <label for="identification">Número de documento <span class="req">*</span></label>
                                <input type="text" id="identification" name="identification" class="control" autocomplete="off" placeholder="Ej. 1090000000" inputmode="numeric" value="{{ Auth::check() ? $user->identification : '' }}">
                                <label id="identification-error" class="error" for="identification"></label>
                            </div>

                            <div class="field">
                                <label for="cellphone">Celular <span class="req">*</span></label>
                                <div class="phone-group">
                                    <span class="pp">+57</span>
                                    <input type="text" id="cellphone" name="cellphone" class="control" autocomplete="tel-national" placeholder="300 000 0000" inputmode="tel" maxlength="10" value="{{ Auth::check() ? $user->cellphone : '' }}">
                                </div>
                                <label id="cellphone-error" class="error" for="cellphone"></label>
                            </div>

                            <div class="field">
                                <label for="citie">Ciudad <span class="req">*</span></label>
                                @if (Auth::check())
                                    {!! Form::select('citie', $cities, $citie, ['id' => 'citie', 'class' => 'select2']) !!}
                                @else
                                    {!! Form::select('citie', $cities, null, ['id' => 'citie', 'class' => 'select2']) !!}
                                @endif
                                <label id="citie-error" class="error" for="citie"></label>
                            </div>

                            <div class="field full">
                                <label for="address">Dirección <span class="req">*</span></label>
                                <input type="text" id="address" name="address" class="control" autocomplete="street-address" placeholder="Cra 22 # 35 – 40 Int. 224" value="{{ Auth::check() ? $user->address : '' }}">
                                <label id="address-error" class="error" for="address"></label>
                            </div>

                            @if (!Auth::check())
                                <div class="field full">
                                    <label for="email">Correo electrónico <span class="req">*</span></label>
                                    <div class="lead-icon">
                                        <i class="fas fa-envelope"></i>
                                        <input type="email" id="email" name="email" class="control" autocomplete="email" placeholder="tucorreo@ejemplo.com" inputmode="email">
                                    </div>
                                    <label id="email-error" class="error" for="email"></label>
                                </div>

                                <div class="field">
                                    <label for="password">Contraseña <span class="req">*</span></label>
                                    <div class="pw-wrap">
                                        <input type="password" id="password" name="password" class="control" autocomplete="new-password" placeholder="Crea tu contraseña">
                                        <button type="button" class="pw-toggle" id="pwToggle" aria-label="Mostrar contraseña"><i class="fas fa-eye"></i></button>
                                    </div>
                                    <label id="password-error" class="error" for="password"></label>
                                </div>
                            @endif

                            <div class="field {{ Auth::check() ? 'full' : '' }}">
                                <label for="company">Empresa (opcional)</label>
                                <input type="text" id="company" name="company" class="control" autocomplete="organization" placeholder="Nombre de tu empresa" value="{{ Auth::check() ? $user->company : '' }}">
                                <label id="company-error" class="error" for="company"></label>
                            </div>

                            <div class="field full">
                                <label class="terms-check">
                                    <input type="checkbox" id="terms" name="terms" value="1">
                                    <span>Acepto los <a href="{{ route('terms') }}" target="_blank">términos y condiciones</a> y la política de tratamiento de datos.</span>
                                </label>
                                <label id="terms-error" class="error" for="terms"></label>

                                <label class="terms-check terms-check-soft">
                                    <input type="checkbox" id="newsletter" name="newsletter" value="1" {{ Auth::check() && $user->newsletter_notification ? 'checked' : '' }}>
                                    <span>Quiero recibir novedades, nuevos cursos y promociones por correo.</span>
                                </label>
                            </div>

                        </div>
                    </form>

                    {{-- Garantías / confianza --}}
                    <div class="checkout-trust">
                        <div class="ct-item"><span class="ic"><i class="fas fa-bolt"></i></span><div class="tx"><b>Acceso inmediato</b><span>Empieza a estudiar apenas se confirme el pago.</span></div></div>
                        <div class="ct-item"><span class="ic"><i class="fas fa-award"></i></span><div class="tx"><b>Certificado al finalizar</b><span>Descarga tu certificado al aprobar el curso.</span></div></div>
                        <div class="ct-item"><span class="ic"><i class="fas fa-lock"></i></span><div class="tx"><b>Pago seguro</b><span>Procesado por Wompi. No almacenamos tu tarjeta.</span></div></div>
                        <div class="ct-item"><span class="ic"><i class="fab fa-whatsapp"></i></span><div class="tx"><b>Soporte cuando lo necesites</b><span>Escríbenos por WhatsApp ante cualquier duda.</span></div></div>
                    </div>
                </div>

                {{-- ===== Columna derecha: resumen + sandbox ===== --}}
                <div class="cartx-summary-col cartx-reveal">

                    <div class="summary">
                        <div class="summary-head">
                            <div class="eyebrow">Tu compra</div>
                            <h3>Resumen orden</h3>
                        </div>
                        <div class="summary-body">

                            @foreach($items as $it)
                                @php $liCompare = ! empty($it->compare) && $it->compare > $it->unit; @endphp
                                <div class="line-item">
                                    <div class="li-main">
                                        <div class="li-tags">
                                            <span class="li-tag">{{ $it->type === 'bundle' ? 'Paquete' : 'Curso' }}</span>
                                            @if($liCompare)<span class="li-off">-{{ round(($it->compare - $it->unit) / $it->compare * 100) }}%</span>@endif
                                        </div>
                                        <div class="li-name">{{ $it->title }}</div>
                                        @if($it->qty > 1)
                                            <div class="li-qty">{{ $it->qty }} × ${{ number_format($it->unit, 0, ',', '.') }} COP</div>
                                        @endif
                                    </div>
                                    <div class="li-price">
                                        ${{ number_format($it->amount, 0, ',', '.') }} COP
                                        @if($liCompare)<s>${{ number_format($it->compare_amount, 0, ',', '.') }} COP</s>@endif
                                    </div>
                                </div>
                            @endforeach

                            <div class="totals">
                                <div class="trow">
                                    <span class="lbl">Subtotal</span>
                                    <span class="val subtotal-price">0 COP</span>
                                </div>
                                <div class="trow">
                                    <span class="lbl">Descuento</span>
                                    <span class="val cartx-free discount-price">0 COP</span>
                                </div>
                                <div class="totals-divider"></div>
                                <div class="trow total">
                                    <span class="lbl">Total</span>
                                    <span class="val total-price">0 COP</span>
                                </div>
                            </div>

                            {{-- Cupón --}}
                            <div class="coupon">
                                @if (isset($_COOKIE['coupon_code']))
                                    <div class="coupon-applied">
                                        <div class="ic"><i class="fas fa-check"></i></div>
                                        <div class="txt">
                                            <b>Cupón {{ $_COOKIE['coupon_code'] }} aplicado</b>
                                            <span>Descuento aplicado a tu orden</span>
                                        </div>
                                        <button type="button" class="rm clear-coupon-btn" aria-label="Quitar cupón"><i class="fas fa-times"></i></button>
                                    </div>
                                @else
                                    <button type="button" id="toggleCoupon" class="coupon-toggle">
                                        <i class="fas fa-tag"></i> ¿Tienes un cupón de descuento?
                                        <span class="chev"><i class="fas fa-chevron-down"></i></span>
                                    </button>
                                @endif

                                <div id="couponFields" class="d-none">
                                    <form id="formCoupons" class="coupon-form" enctype="multipart/form-data" role="form" onSubmit="return false">
                                        @csrf
                                        <input type="text" name="code" id="code" placeholder="CÓDIGO" class="control coupon-input" required>
                                        <button type="submit" class="coupon-apply apply-coupon-btn">Aplicar</button>
                                        <button type="button" class="coupon-apply clear-coupon-btn d-none" style="background:transparent;color:var(--muted);"><i class="fas fa-times"></i></button>
                                    </form>
                                    <label id="code-error" class="coupon-msg err d-none" for="code"></label>
                                </div>
                            </div>

                            <button type="submit" class="pay-btn btn-disabled" id="addPayments">
                                @if($total <= 0)
                                    <i class="fas fa-graduation-cap"></i> Inscribirme gratis
                                @else
                                    REALIZAR PAGO
                                @endif
                            </button>

                            @if($total > 0)
                            <div class="trust">
                                <i class="fas fa-shield-halved"></i> Pago 100% seguro procesado con Wompi
                            </div>
                            @endif

                            @if(setting('page_cellphone'))
                            <div class="summary-foot">
                                <span class="lbl">Para más detalles</span>
                                <a class="phone" href="tel:{{ setting('page_cellphone') }}">
                                    <i class="fas fa-phone"></i> {{ setting('page_cellphone') }}
                                </a>
                            </div>
                            @endif

                        </div>
                    </div>

                    @if(setting('wompi_sandbox') === 'true' && $total > 0)
                    <div class="sandbox">
                        <div class="sandbox-head" id="sandboxHead">
                            <span class="sandbox-badge"><i class="fas fa-flask"></i> Modo prueba</span>
                            <span class="ttl">Tarjetas de prueba</span>
                            <span class="chev open"><i class="fas fa-chevron-down"></i></span>
                        </div>
                        <div class="sandbox-body" id="sandboxBody">
                            <p class="intro">Estás en el <b>entorno sandbox de Wompi</b>. Los pagos <b>no son reales</b>: usa estos datos para simular el resultado.</p>
                            <div class="test-list">
                                <div class="test-row ok">
                                    <span class="dot"></span>
                                    <div class="info">
                                        <div class="h"><b>Pago aprobado</b></div>
                                        <div class="meta">
                                            <span class="chip">4242 4242 4242 4242</span>
                                            <span class="chip"><span class="k">Exp</span>12/29</span>
                                            <span class="chip"><span class="k">CVV</span>123</span>
                                            <span class="chip"><span class="k">OTP</span>123456</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="test-row no">
                                    <span class="dot"></span>
                                    <div class="info">
                                        <div class="h"><b>Pago declinado</b></div>
                                        <div class="meta">
                                            <span class="chip">4111 1111 1111 1111</span>
                                            <span class="chip"><span class="k">Exp</span>12/29</span>
                                            <span class="chip"><span class="k">CVV</span>123</span>
                                            <span class="chip"><span class="k">OTP</span>123456</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="test-row pse">
                                    <span class="dot"></span>
                                    <div class="info">
                                        <div class="h"><b>PSE</b></div>
                                        <p class="test-note">Selecciona cualquier banco de prueba · Persona Natural.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>

            </div>
        </div>
    </section>

</main>

<div class="payment d-none"></div>

@endsection

@push('scripts')

    <script>
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        // Conversión: InitiateCheckout al abrir el checkout (si hay pixels cargados).
        (function () {
            var value = {{ (float) $total }};
            if (typeof fbq !== 'undefined') {
                fbq('track', 'InitiateCheckout', { value: value, currency: 'COP' });
            }
            if (typeof ttq !== 'undefined') {
                ttq.track('InitiateCheckout', { value: value, currency: 'COP' });
            }
        })();

        $(document).ready(function () {

            function loaderCheckout() {
                var subtotal = parseFloat($('#subtotal').val()) || 0;
                var formatted = subtotal.toLocaleString('es-CO');
                $('.subtotal-price').text('$ ' + formatted + ' COP');
                $('.total-price').text('$ ' + formatted + ' COP');
            }
            loaderCheckout();

            // Habilitar "Realizar pago" sólo al aceptar términos y condiciones
            function syncPayButton() {
                var accepted = $('#terms').is(':checked');
                $('#addPayments').prop('disabled', !accepted).toggleClass('btn-disabled', !accepted);
            }
            syncPayButton();
            $(document).on('change', '#terms', syncPayButton);

            // Toggle cupón
            $('#toggleCoupon').on('click', function () {
                $('#couponFields').removeClass('d-none');
                $(this).addClass('open');
                setTimeout(function () { $('#code').trigger('focus'); }, 60);
            });

            // Toggle sandbox
            $('#sandboxHead').on('click', function () {
                $('#sandboxBody').toggle();
                $(this).find('.chev').toggleClass('open');
            });

            // Toggle password
            $('#pwToggle').on('click', function () {
                var $i = $('#password');
                var type = $i.attr('type') === 'password' ? 'text' : 'password';
                $i.attr('type', type);
                $(this).find('i').toggleClass('fa-eye fa-eye-slash');
            });

            // select2 ciudad
            $('#citie').select2({
                placeholder: 'Busca tu ciudad…',
                width: '100%',
                minimumInputLength: 0,
                allowClear: false,
                language: {
                    searching: function () { return 'Buscando…'; },
                    noResults: function () { return 'Sin resultados'; },
                    errorLoading: function () { return 'No se pudieron cargar las ciudades'; }
                },
                ajax: {
                    dataType: 'json',
                    url: '{{ route("checkout.cities") }}',
                    delay: 250,
                    data: function (params) { return { term: $.trim(params.term) }; },
                    processResults: function (data) { return { results: data }; },
                    cache: true
                }
            });

            // select2 tipo de documento
            $('#identification_type').select2({
                placeholder: 'Tipo de documento',
                minimumResultsForSearch: Infinity,
                width: '100%'
            });

            // Revalidar los select2 al cambiar para limpiar el error
            $('#citie, #identification_type').on('change', function () {
                if ($('#formCheckouts').data('validator')) {
                    $(this).valid();
                }
            });
        });

        // Quitar cupón
        $(document).on('click', '.clear-coupon-btn', function () {
            $.ajax({
                url: "{{ route('checkout.coupon.clear') }}",
                type: "POST",
                contentType: false,
                processData: false,
                data: [],
                success: function () {
                    $('#code').val('');
                    $('.discount-price').text('$ 0 COP');
                    var subtotal = parseFloat($('#subtotal').val()) || 0;
                    $('#discount').val(0);
                    $('#total').val(subtotal);
                    $('.total-price').text('$ ' + subtotal.toLocaleString('es-CO') + ' COP');
                    $('.coupon-applied').remove();
                    $('.apply-coupon-btn').removeClass('d-none').prop('disabled', false).text('Aplicar');
                    $('.coupon-input').prop('disabled', false);
                }
            });
        });

        jQuery.validator.addMethod("emailExt", function (value) {
            return value.match(/^[a-zA-Z0-9_\.%\+\-]+@[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,3}$/);
        }, 'Porfavor ingrese email valido');

        // Celular colombiano: 10 dígitos que empiezan por 3 (tolera +57 / 57 / espacios)
        jQuery.validator.addMethod("coCel", function (value, element) {
            var v = (value || '').replace(/\D/g, '').replace(/^57/, '');
            return this.optional(element) || /^3\d{9}$/.test(v);
        }, 'Ingresa un celular válido de Colombia (10 dígitos, empieza por 3)');

        // Normalizar el celular al salir del campo (quita +57, espacios, etc.)
        $(document).on('blur', '#cellphone', function () {
            var v = ($(this).val() || '').replace(/\D/g, '').replace(/^57/, '').slice(0, 10);
            $(this).val(v);
        });

        function updateCouponPrice(data) {
            var discount = parseFloat(data['discount']) || 0;
            var total = parseFloat(data['total']) || 0;
            $('#code').val(data['code']);
            $('#discount').val(data['discount']);
            $('#total').val(data['total']);
            $('.discount-price').text('$ ' + discount.toLocaleString('es-CO') + ' COP');
            $('.total-price').text('$ ' + total.toLocaleString('es-CO') + ' COP');
        }

        $("#formCoupons").validate({
            submit: false,
            rules: { code: { required: true, minlength: 6, maxlength: 6 } },
            messages: {
                code: {
                    required: "El codigo es necesario",
                    minlength: "El codigo debe contener 6 caracteres",
                    maxlength: "El codigo debe contener 6 caracteres"
                }
            },
            errorPlacement: function (error, element) {
                $("#" + element.attr("id") + "-error").removeClass('d-none').text(error.text());
            },
            submitHandler: function () {
                $('.apply-coupon-btn').prop('disabled', true).text('Aplicando...');
                $('#code-error').addClass('d-none').text('');

                var formData = new FormData($('#formCoupons')[0]);
                formData.append('code', $("#code").val());

                $.ajax({
                    url: "{{ route('checkout.coupon.apply') }}",
                    type: "POST",
                    contentType: false,
                    processData: false,
                    data: formData,
                    success: function (data) {
                        if (data['success'] == false) {
                            $('#code-error').removeClass('d-none').text(data['message']);
                            $('.apply-coupon-btn').prop('disabled', false).text('Aplicar');
                        } else {
                            updateCouponPrice(data);
                            var code = (data['code'] || $("#code").val() || '').toUpperCase();
                            $('.apply-coupon-btn').prop('disabled', false).text('Aplicar');
                            $('#toggleCoupon').addClass('d-none');
                            $('#couponFields').addClass('d-none');
                            $('.coupon-applied').remove();
                            $('.coupon').prepend(
                                '<div class="coupon-applied">' +
                                    '<div class="ic"><i class="fas fa-check"></i></div>' +
                                    '<div class="txt"><b>Cupón ' + code + ' aplicado</b><span>Descuento aplicado a tu orden</span></div>' +
                                    '<button type="button" class="rm clear-coupon-btn" aria-label="Quitar cupón"><i class="fas fa-times"></i></button>' +
                                '</div>'
                            );
                        }
                    }
                });
            }
        });

        $("#formCheckouts").validate({
            submit: false,
            ignore: [],
            rules: {
                firstname: { required: true, minlength: 2, maxlength: 200 },
                lastname:  { required: true, minlength: 2, maxlength: 200 },
                identification_type: { required: true },
                identification: { required: true, number: true, minlength: 5, maxlength: 20 },
                address:   { required: true, minlength: 10, maxlength: 500 },
                company:   { required: false, minlength: 4, maxlength: 100 },
                cellphone: { required: true, coCel: true },
                email:     { required: true, email: true, emailExt: true },
                password:  { required: true },
                citie:     { required: true },
                terms:     { required: true }
            },
            messages: {
                firstname: { required: "El nombres es necesario", minlength: "Mínimo 2 caracteres", maxlength: "Máximo 200 caracteres" },
                lastname:  { required: "Los apellidos son necesarios", minlength: "Mínimo 2 caracteres", maxlength: "Máximo 200 caracteres" },
                identification_type: { required: "Selecciona el tipo de documento" },
                identification: { required: "El documento es necesario", number: "Sólo números", minlength: "Mínimo 5 dígitos", maxlength: "Máximo 20 dígitos" },
                company:   { minlength: "Mínimo 4 caracteres", maxlength: "Máximo 100 caracteres" },
                address:   { required: "La dirección es necesaria", minlength: "Mínimo 10 caracteres", maxlength: "Máximo 500 caracteres" },
                cellphone: { required: "El celular es necesario", coCel: "Celular inválido (10 dígitos, empieza por 3)" },
                email:     { required: "El email es necesario", email: "Por favor ingrese email valido" },
                citie:     { required: "El campo ciudad es necesario" },
                password:  { required: "La contraseña es necesaria" },
                terms:     { required: "Debes aceptar los términos y condiciones" }
            },
            errorPlacement: function (error, element) {
                $("#" + element.attr("id") + "-error").text(error.text());
            },
            success: function (label, element) {
                $("#" + $(element).attr("id") + "-error").text('');
            },
            submitHandler: function () {

                $('#addPayments').addClass('btn-disabled-payment').prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin"></i> Generando url de pago...');

                var formData = new FormData($('#formCheckouts')[0]);
                formData.append('firstname', $("#firstname").val());
                formData.append('lastname', $("#lastname").val());
                formData.append('identification', $("#identification").val());
                formData.append('identification_type', $("#identification_type").val());
                formData.append('terms', $("#terms").is(':checked') ? '1' : '');
                formData.append('newsletter', $("#newsletter").is(':checked') ? '1' : '0');
                formData.append('cellphone', $("#cellphone").val());
                formData.append('password', $("#password").val());
                formData.append('address', $("#address").val());
                formData.append('email', $("#email").val());
                formData.append('citie', $("#citie").val());

                $.ajax({
                    url: "{{ route('checkout.register') }}",
                    type: "POST",
                    contentType: false,
                    processData: false,
                    data: formData,
                    success: function (data) {

                        if (data == "email") {
                            var em = encodeURIComponent($('#email').val() || '');
                            $('#email-error').removeClass('d-none')
                                .html('Este correo ya está registrado. <a href="{{ route('login') }}?email=' + em + '" style="text-decoration:underline;font-weight:700">Inicia sesión</a> para continuar tu compra.');
                            $('#addPayments').removeClass('btn-disabled-payment').prop('disabled', false)
                                .html('REALIZAR PAGO');
                            return;
                        }

                        if (data == "success") {
                            $.ajax({
                                url: "{{ route('checkout.generate') }}",
                                type: "POST",
                                dataType: "json",
                                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                                success: function (res) {
                                    if (res && res.redirect) {
                                        // Curso gratis -> confirmación; con costo -> Web Checkout de Wompi
                                        window.location.href = res.redirect;
                                        return;
                                    }
                                    $('#addPayments').removeClass('btn-disabled-payment').prop('disabled', false)
                                        .html('REALIZAR PAGO');
                                    alert('No se pudo generar la orden. Intenta de nuevo.');
                                },
                                error: function (jqXHR) {
                                    $('#addPayments').removeClass('btn-disabled-payment').prop('disabled', false)
                                        .html('REALIZAR PAGO');
                                    alert(jqXHR.status == 422 ? 'Tu carrito está vacío.' : 'Error al generar la orden de pago.');
                                }
                            });
                        }
                    },
                    error: function (jqXHR) {
                        $('#addPayments').removeClass('btn-disabled-payment').prop('disabled', false)
                            .html('REALIZAR PAGO');

                        // Limpiar errores previos por campo
                        $('label.error').addClass('d-none').text('');

                        if (jqXHR.status === 422 && jqXHR.responseJSON && jqXHR.responseJSON.errors) {
                            var errors = jqXHR.responseJSON.errors;
                            var firstField = null;
                            $.each(errors, function (field, messages) {
                                var msg = Array.isArray(messages) ? messages[0] : messages;
                                var $lbl = $('#' + field + '-error');
                                if ($lbl.length) {
                                    $lbl.removeClass('d-none').text(msg);
                                    if (!firstField) firstField = field;
                                }
                            });
                            if (firstField) {
                                var $el = $('#' + firstField);
                                if ($el.length) {
                                    $el.trigger('focus');
                                    $('html,body').animate({ scrollTop: $el.offset().top - 130 }, 300);
                                }
                            } else {
                                var first = Object.values(errors)[0];
                                alert(Array.isArray(first) ? first[0] : first);
                            }
                        } else {
                            alert('No se pudo procesar el registro. Revisa tus datos e intenta de nuevo.');
                        }
                    }
                });
            }
        });

        $('#addPayments').on('click', function () {
            $('#formCheckouts').submit();
        });
    </script>

@endpush
