<footer class="main-footer style-3 background-luminosity overlay-black-dark" style="background-image: url(/pages/images/footer/slider-bg.png)">
    <div class="container">

        <div class="row large-gap justify-content-between ">
            <div class="col-lg-4 col-sm-12">
                <div class="footer-widget about-widget wow fadeInUp" data-wow-delay="0.2s">

                    <h5 class="footer-title">Sobre nosotros</h5>

                    @if (setting('page_description')!=null)
                        {!! setting('page_description') !!}
                    @endif


                </div>
            </div>
            <div class="col-lg-3 col-sm-12">
                <div class="footer-widget menu-widget wow fadeInUp" data-wow-delay="0.2s">
                    <h5 class="footer-title">Accesos</h5>
                    <ul>
                        <li><a href="{{ route('about') }}">Sobre nosotros</a></li>
                        <li><a href="{{ route('faqs') }}">Preguntas frecuentes</a></li>
                        <li><a href="{{ route('terms') }}">Términos y condiciones</a></li>
                        <li><a href="{{ route('contacts') }}">Contáctenos</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-4 col-sm-12">
                <div class="footer-widget contact-info-widget wow fadeInUp" data-wow-delay="0.2s">
                    <h5 class="footer-title">Contacta con nosotros</h5>
                    <div class="footer-contact-list">

                        @if(setting('page_phone'))
                        <a href="tel:+{{ setting('page_phone') }}" class="footer-contact-item">
                            <span class="footer-contact-icon"><i class="fas fa-phone"></i></span>
                            <span>+{{ setting('page_phone') }}</span>
                        </a>
                        @endif

                        @if(setting('page_whatsapp'))
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', setting('page_whatsapp')) }}" target="_blank" class="footer-contact-item">
                            <span class="footer-contact-icon"><i class="fab fa-whatsapp"></i></span>
                            <span>{{ setting('page_whatsapp') }}</span>
                        </a>
                        @endif

                        @if(setting('page_email'))
                        <a href="mailto:{{ setting('page_email') }}" class="footer-contact-item">
                            <span class="footer-contact-icon"><i class="fas fa-envelope"></i></span>
                            <span>{{ setting('page_email') }}</span>
                        </a>
                        @endif

                        @if(setting('page_address'))
                        <div class="footer-contact-item">
                            <span class="footer-contact-icon"><i class="fas fa-location-dot"></i></span>
                            <span>{{ setting('page_address') }}</span>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="copyright-area rel wow fadeInUp" data-wow-delay="0.2s">
        <div class="container">
            <div class="copyright-inner">
                <p>{{ setting('copyright') }} <a href="https://mimmers.com">Mimmers</a> | Todos los derechos reservados.</p>

            </div>
        </div>
        <!-- Scroll Top Button -->
        <button class="scroll-top scroll-to-target" data-target="html" ><span class="fas fa-angle-double-up"></span></button>
    </div>
</footer>