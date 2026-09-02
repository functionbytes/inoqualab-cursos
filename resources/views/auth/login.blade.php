@extends('layouts.pages')

@section('title', 'Ingresar')

@section('content')
{{-- Variante B elegida vía /design: split-screen, formulario a la izquierda
     y panel de marca (navy) con beneficios de la plataforma a la derecha.
     El panel de marca se oculta debajo de 992px (ver .login-split-brand en
     style.css) para priorizar el formulario en móvil. --}}
<section class="account login-split-section">
    <div class="container">
        <div class="login-split">
            <div class="login-split-form">
                <div class="login-split-form-inner aos-init aos-animate" data-aos="fade-up" data-aos-duration="800">
                    <div class="account__header pb-30">
                        <h3 class="pb-5">Ingrese a su cuenta</h3>
                        <p>¡Bienvenido de nuevo! Por favor ingrese sus datos.</p>
                    </div>

                    <form class="account__form needs-validation" method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="row g-4">
                            <div class="col-lg-12">
                                <div class="input-group">
                                    <input class="form-control" id="email" type="text" name="email" placeholder="Correo electrónico o cedula" required="">
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="input-group">
                                    <input class="form-control" id="password" type="password" name="password" placeholder="Ingresar contraseña" required="">
                                    <button type="button" id="btnToggle" class="toggle">
                                        <i id="eyeIcon" class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="account__form-passcheck">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" value="1" id="terms-check" name="remember">
                                <label for="terms-check" class="form-check-label">
                                    Recordarme
                                </label>

                            </div>
                            <div class="forget-password">
                                <a href="{{ route('password.reset') }}">¿Has olvidado tu contraseña?</a>
                            </div>
                        </div>

                        @if ($errors->has('email'))
                            <div class="notification errors closeable">
                                <p>{{ $errors->first('email') }}</p>
                                <a class="close"></a>
                            </div>
                        @endif

                        @if ($errors->has('password'))
                            <div class="notification errors closeable">
                                <p>{{ $errors->first('password') }}</p>
                                <a class="close"></a>
                            </div>
                        @endif


                        <button type="submit" class="trk-btn trk-btn--border trk-btn--secondary1 d-block mt-4">Ingresar</button>
                    </form>

                </div>
            </div>

            {{-- El contenido queda alineado al .container del sitio (igual que el
                 formulario), pero el fondo navy debe llegar hasta el borde derecho
                 de la ventana -- .login-split-brand-bleed vive en este wrapper sin
                 overflow:hidden para no ser recortado por el que sí necesita
                 .login-split-brand para sus círculos decorativos. --}}
            <div class="login-split-brand-wrap">
                <div class="login-split-brand-bleed"></div>
                <div class="login-split-brand">
                    <div class="login-split-brand-inner">
                        <h2>Continúa tu proceso de <span>certificación</span></h2>
                        <p>Accede a tus cursos, certificados y avances desde un solo lugar, disponible cuando lo necesites.</p>
                        <ul class="login-split-benefits">
                            <li><span class="lsb-ic"><i class="fas fa-check" aria-hidden="true"></i></span> Certificados verificables al finalizar</li>
                            <li><span class="lsb-ic"><i class="fas fa-check" aria-hidden="true"></i></span> Acceso a tu progreso en cualquier momento</li>
                            <li><span class="lsb-ic"><i class="fas fa-check" aria-hidden="true"></i></span> Soporte y contenidos actualizados</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection




@push('scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            // Alternar visibilidad de la contraseña
            $('#btnToggle').on('click', function () {
                const passwordInput = $('#password');
                const eyeIcon = $('#eyeIcon');
                const isPassword = passwordInput.attr('type') === 'password';

                passwordInput.attr('type', isPassword ? 'text' : 'password');

                // Cambiar el icono entre ojo abierto y cerrado
                if (isPassword) {
                    eyeIcon.removeClass('fa-eye').addClass('fa-eye-slash'); // Ojo cerrado
                } else {
                    eyeIcon.removeClass('fa-eye-slash').addClass('fa-eye'); // Ojo abierto
                }
            });
        });


    </script>

@endpush
