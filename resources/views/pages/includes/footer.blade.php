<style>
.site-footer {
    position: relative;
    background: radial-gradient(1100px 380px at 15% -10%, #123049 0%, #081A28 55%, #061420 100%);
    color: #cfd8e3;
    padding: 64px 0 0;
    overflow: hidden;
}
.site-footer a { text-decoration: none; }

.sf-top {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 28px 40px;
    padding-bottom: 32px;
}
.sf-brand { display: flex; align-items: center; gap: 14px; }
.sf-brand img { height: 40px; width: auto; display: block; filter: brightness(0) invert(1); }

.sf-contact-row { display: flex; flex-wrap: wrap; align-items: center; gap: 26px 36px; }
.sf-contact { display: flex; align-items: center; gap: 14px; color: #cfd8e3; }
.sf-ico {
    flex: 0 0 auto;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(0,139,205,.16);
    color: #4fc3f7;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}
.sf-contact-text { display: flex; flex-direction: column; line-height: 1.3; }
.sf-contact-text small { font-size: 12.5px; color: #93a5b8; font-weight: 600; }
.sf-contact-text b { font-size: 15px; color: #fff; font-weight: 800; }

.sf-divider { border: none; border-top: 1px solid rgba(255,255,255,.12); margin: 0; }

.sf-grid {
    display: grid;
    grid-template-columns: 1.3fr 1fr 1fr;
    gap: 36px;
    padding: 40px 0 44px;
}
@media (max-width: 991px) { .sf-grid { grid-template-columns: 1fr 1fr; } }
@media (max-width: 575px) { .sf-grid { grid-template-columns: 1fr; } }

.sf-about { font-size: 14px; line-height: 1.7; color: #a9b7c6; margin-bottom: 20px; }
.sf-about p:last-child { margin-bottom: 0; }

.sf-social-title { display: block; color: #fff; font-weight: 700; margin-bottom: 12px; font-size: 14.5px; }
.sf-social-icons { display: flex; gap: 10px; }
.sf-social-icon {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    border: 1.5px solid rgba(255,255,255,.22);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 14px;
    transition: .2s;
}
.sf-social-icon:hover { background: #008bce; border-color: #008bce; color: #fff; }

.sf-title { color: #fff; font-size: 17px; font-weight: 800; margin: 0 0 18px; }
.sf-links { list-style: none; margin: 0; padding: 0; }
.sf-links li { margin-bottom: 11px; }
.sf-links li a { color: #a9b7c6; font-size: 14px; transition: .2s; }
.sf-links li a:hover { color: #4fc3f7; }

.site-footer .copyright-area { border-top: 1px solid rgba(255,255,255,.1); padding: 20px 0; position: relative; }
.site-footer .copyright-inner p { margin: 0; text-align: center; font-size: 13px; color: #93a5b8; }
.site-footer .copyright-inner a { color: #cfd8e3; font-weight: 700; }
.site-footer .copyright-inner a:hover { color: #4fc3f7; }
</style>

<footer class="site-footer">
    <div class="container">

        <div class="sf-top">
            <div class="sf-brand">
                <a href="{{ route('index') }}">
                    <img src="{{ getlogo() }}" alt="{{ setting('page_title') }}">
                </a>
            </div>

            <div class="sf-contact-row">
                @if(setting('page_phone'))
                    <a href="tel:+{{ setting('page_phone') }}" class="sf-contact">
                        <span class="sf-ico"><i class="fas fa-phone"></i></span>
                        <span class="sf-contact-text">
                            <small>Teléfono</small>
                            <b>+{{ setting('page_phone') }}</b>
                        </span>
                    </a>
                @endif

                @if(setting('page_email'))
                    <a href="mailto:{{ setting('page_email') }}" class="sf-contact">
                        <span class="sf-ico"><i class="fas fa-envelope"></i></span>
                        <span class="sf-contact-text">
                            <small>Correo electrónico</small>
                            <b>{{ setting('page_email') }}</b>
                        </span>
                    </a>
                @endif

                @if(setting('page_address'))
                    <div class="sf-contact">
                        <span class="sf-ico"><i class="fas fa-location-dot"></i></span>
                        <span class="sf-contact-text">
                            <small>Ubicación</small>
                            <b>{{ setting('page_address') }}</b>
                        </span>
                    </div>
                @endif
            </div>
        </div>

        <hr class="sf-divider">

        <div class="sf-grid">
            <div class="sf-col">
                @if (setting('page_description'))
                    <div class="sf-about">{!! setting('page_description') !!}</div>
                @endif

                @if(setting('social_media_facebook') || setting('social_media_instagram') || setting('social_media_twitter') || setting('social_media_linkedin') || setting('social_media_youtube') || setting('page_whatsapp'))
                    <div>
                        <span class="sf-social-title">Síguenos en redes:</span>
                        <div class="sf-social-icons">
                            @if(setting('social_media_facebook'))
                                <a href="{{ setting('social_media_facebook') }}" target="_blank" rel="noopener" class="sf-social-icon" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                            @endif
                            @if(setting('social_media_instagram'))
                                <a href="{{ setting('social_media_instagram') }}" target="_blank" rel="noopener" class="sf-social-icon" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                            @endif
                            @if(setting('social_media_twitter'))
                                <a href="{{ setting('social_media_twitter') }}" target="_blank" rel="noopener" class="sf-social-icon" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                            @endif
                            @if(setting('social_media_linkedin'))
                                <a href="{{ setting('social_media_linkedin') }}" target="_blank" rel="noopener" class="sf-social-icon" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                            @endif
                            @if(setting('social_media_youtube'))
                                <a href="{{ setting('social_media_youtube') }}" target="_blank" rel="noopener" class="sf-social-icon" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                            @endif
                            @if(setting('page_whatsapp'))
                                <a href="https://wa.me/{{ preg_replace('/\D/', '', setting('page_whatsapp')) }}" target="_blank" rel="noopener" class="sf-social-icon" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <div class="sf-col">
                <h5 class="sf-title">Enlaces rápidos</h5>
                <ul class="sf-links">
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
                <div class="sf-col">
                    <h5 class="sf-title">Nuestros cursos</h5>
                    <ul class="sf-links">
                        @foreach($allcourses->take(5) as $course)
                            <li><a href="{{ route('courses.view', [$course->slack]) }}">{{ Str::limit($course->title, 32) }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif

        </div>
    </div>

    <div class="copyright-area rel wow fadeInUp" data-wow-delay="0.2s">
        <div class="container">
            <div class="copyright-inner">
                <p>{{ setting('copyright') }} <a href="https://mimmers.com">Mimmers</a> | Todos los derechos reservados.</p>
            </div>
        </div>
        <!-- Scroll Top Button -->
        <button class="scroll-top scroll-to-target" data-target="html" aria-label="Volver arriba"><span class="fas fa-angle-double-up" aria-hidden="true"></span></button>
    </div>
</footer>
