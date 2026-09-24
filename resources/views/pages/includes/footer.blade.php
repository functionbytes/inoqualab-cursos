<footer class="iq-footer-sunex" role="contentinfo">
    <div class="container">

        <div class="iq-footer-sunex-box">
            <div class="iq-footer-about">
                @if(setting('page_description'))
                    <div class="iq-footer-about-content">
                        {!! clean(setting('page_description'), 'content') !!}
                    </div>
                @endif

                @if(setting('social_media_facebook') || setting('social_media_instagram') || setting('social_media_twitter') || setting('social_media_linkedin') || setting('social_media_youtube') || setting('page_whatsapp'))
                    <div class="iq-footer-social">
                        <h2>Síguenos en redes</h2>
                        <ul>
                            @if(setting('social_media_facebook'))
                                <li><a href="{{ setting('social_media_facebook') }}" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a></li>
                            @endif
                            @if(setting('social_media_instagram'))
                                <li><a href="{{ setting('social_media_instagram') }}" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a></li>
                            @endif
                            @if(setting('social_media_twitter'))
                                <li><a href="{{ setting('social_media_twitter') }}" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-twitter"></i></a></li>
                            @endif
                            @if(setting('social_media_linkedin'))
                                <li><a href="{{ setting('social_media_linkedin') }}" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a></li>
                            @endif
                            @if(setting('social_media_youtube'))
                                <li><a href="{{ setting('social_media_youtube') }}" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube"></i></a></li>
                            @endif
                            @if(setting('page_whatsapp'))
                                <li><a href="https://wa.me/{{ preg_replace('/\D/', '', setting('page_whatsapp')) }}" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a></li>
                            @endif
                        </ul>
                    </div>
                @endif
            </div>

            <div class="iq-footer-links-box">
                <div class="iq-footer-links">
                    <h2>Enlaces rápidos</h2>
                    <ul>
                        <li><a href="{{ route('index') }}">Inicio</a></li>
                        <li><a href="{{ route('about') }}">Sobre nosotros</a></li>
                        <li><a href="{{ route('courses') }}">Cursos</a></li>
                        @if($hasActiveBundles)
                            <li><a href="{{ route('bundles') }}">Paquetes de cursos</a></li>
                        @endif
                        <li><a href="{{ route('contacts') }}">Contáctenos</a></li>
                    </ul>
                </div>

                @if($allcourses->count())
                    <div class="iq-footer-links">
                        <h2>Nuestros cursos</h2>
                        <ul>
                            @foreach($allcourses->take(5) as $course)
                                <li><a href="{{ route('courses.view', [$course->slack]) }}">{{ Str::limit(str($course->title)->lower()->ucfirst(), 32) }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="iq-footer-links">
                    <h2>Legal</h2>
                    <ul>
                        <li><a href="{{ route('faqs') }}">Preguntas frecuentes</a></li>
                        <li><a href="{{ route('terms') }}">Términos y condiciones</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="iq-footer-contact-list">
            @if(setting('page_phone'))
                <div class="iq-footer-contact-item">
                    <div class="iq-footer-contact-icon"><i class="fas fa-phone" aria-hidden="true"></i></div>
                    <div class="iq-footer-contact-content">
                        <p>Teléfono</p>
                        <h3><a href="tel:+{{ setting('page_phone') }}">+{{ setting('page_phone') }}</a></h3>
                        @if(setting('page_whatsapp'))
                            <p class="iq-footer-contact-extra">
                                <a href="https://wa.me/{{ preg_replace('/\D/', '', setting('page_whatsapp')) }}" target="_blank" rel="noopener">WhatsApp {{ setting('page_whatsapp') }}</a>
                            </p>
                        @endif
                    </div>
                </div>
            @endif

            @if(setting('page_email'))
                <div class="iq-footer-contact-item">
                    <div class="iq-footer-contact-icon"><i class="fas fa-envelope" aria-hidden="true"></i></div>
                    <div class="iq-footer-contact-content">
                        <p>Correo electrónico</p>
                        {{-- <wbr> tras la @: si no cabe, el correo parte ahí y no a mitad de palabra --}}
                        <h3><a href="mailto:{{ setting('page_email') }}">{!! str_replace('@', '@<wbr>', e(setting('page_email'))) !!}</a></h3>
                    </div>
                </div>
            @endif

            @if(setting('page_address'))
                <div class="iq-footer-contact-item">
                    <div class="iq-footer-contact-icon"><i class="fas fa-location-dot" aria-hidden="true"></i></div>
                    <div class="iq-footer-contact-content">
                        <p>Ubicación</p>
                        <h3>{{ setting('page_address') }}</h3>
                    </div>
                </div>
            @endif
        </div>

        <div class="iq-footer-copyright">
            <p>{{ setting('copyright') }} <a href="https://mimmers.com">Mimmers</a> | Todos los derechos reservados.</p>
        </div>
    </div>

    <!-- Scroll Top Button -->
    <button class="scroll-top scroll-to-target" data-target="html" aria-label="Volver arriba"><span class="fas fa-angle-double-up" aria-hidden="true"></span></button>
</footer>
