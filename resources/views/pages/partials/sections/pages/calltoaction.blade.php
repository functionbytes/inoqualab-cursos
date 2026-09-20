<section class="call-to-action rel z-2 mt-705  rmt-95">
    <div class="container">
        <div class="call-to-action-inner wow zoomIn delay-0-2s">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="notification rmb-25">
                        <img src="/pages/images/shapes/notification.svg" alt="Notification">
                        <div class="content">
                            @auth
                                <h4>Continúa aprendiendo</h4>
                                <p>Accede a tus cursos y retoma donde lo dejaste.</p>
                            @else
                                <h4>¿Ya tienes una cuenta?</h4>
                                <p>Ingresa para ver tus cursos y seguir aprendiendo.</p>
                            @endauth
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-right">
                    @auth
                        <a href="{{ route('customers.dashboard') }}" class="theme-btn style-four">MIS CURSOS <i class="fas fa-arrow-right"></i></a>
                    @else
                        <a href="{{ route('login') }}" class="theme-btn style-four">INGRESAR <i class="fas fa-arrow-right"></i></a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</section>