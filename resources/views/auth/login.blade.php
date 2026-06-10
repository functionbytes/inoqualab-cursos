@extends('layouts.pages')

@section('title', 'Ingresar')

@section('content')
<section class="account pt-150 padding-bottom">
    <div class="container-fluid">
        <div class="account__wrapper aos-init aos-animate" data-aos="fade-up" data-aos-duration="800">
            <div class="row g-4">
                <div class="col-lg-12">
                    <div class="account__content">
                        <!-- account tittle -->
                        <div class="account__header pb-30">
                            <h3 class="pb-5">Ingrese a su cuenta</h3>
                            <p>¡Bienvenido de nuevo! Por favor ingrese sus datos.</p>
                        </div>
                        <!-- account form -->
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

