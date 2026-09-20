@extends('layouts.pages')

@section('title', 'Facturación')

{{-- select2 (#citie, #identification_type más abajo) solo se usa en esta
     vista -- se saca del layout global (layouts/pages.blade.php) para no
     bloquear el render de cada página pública con un widget de esta pantalla. --}}
@push('css')
    <link rel="stylesheet" href="{{ url('/pages/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('pages/css/views/payments/register.css') }}">
@endpush

@section('content')

<main class="cartx"
      id="checkoutPage"
      data-total="{{ (float) $total }}"
      data-is-auth="{{ Auth::check() ? '1' : '0' }}"
      data-auth-email="{{ Auth::check() ? $user->email : '' }}"
      data-capture-lead-url="{{ route('cart.capture-lead') }}"
      data-cities-url="{{ route('checkout.cities') }}"
      data-coupon-clear-url="{{ route('checkout.coupon.clear') }}"
      data-coupon-apply-url="{{ route('checkout.coupon.apply') }}"
      data-register-url="{{ route('checkout.register') }}"
      data-generate-url="{{ route('checkout.generate') }}"
      data-login-url="{{ route('login') }}">

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
                                        <button type="button" class="coupon-apply clear-coupon-btn clear-coupon-btn--ghost d-none"><i class="fas fa-times"></i></button>
                                    </form>
                                    <label id="code-error" class="coupon-msg err d-none" for="code"></label>
                                </div>
                            </div>

                            <label id="form-error" class="error checkout-form-error d-none"></label>

                            <button type="submit" class="pay-btn btn-disabled" id="addPayments">
                                @if($total <= 0)
                                    Inscribirme gratis
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

                    {{-- Garantías / confianza --}}
                    <div class="checkout-trust">
                        <div class="ct-item"><span class="ic">@include('customers.includes.icon', ['name' => 'bolt'])</span><div class="tx"><b>Acceso inmediato</b><span>Empieza a estudiar apenas se confirme el pago.</span></div></div>
                        <div class="ct-item"><span class="ic">@include('customers.includes.icon', ['name' => 'award'])</span><div class="tx"><b>Certificado al finalizar</b><span>Descarga tu certificado al aprobar el curso.</span></div></div>
                        <div class="ct-item"><span class="ic">@include('customers.includes.icon', ['name' => 'lock'])</span><div class="tx"><b>Pago seguro</b><span>Procesado por Wompi. No almacenamos tu tarjeta.</span></div></div>
                        <div class="ct-item"><span class="ic">@include('customers.includes.icon', ['name' => 'whatsapp'])</span><div class="tx"><b>Soporte cuando lo necesites</b><span>Escríbenos por WhatsApp ante cualquier duda.</span></div></div>
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

    <script src="{{ url('pages/js/select2.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('pages/js/views/payments/register.js') }}"></script>

@endpush
