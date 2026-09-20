<section class="contact-cta-section contact-cta-section--dark py-80 rpb-60">
    <div class="container">
        <div class="row justify-content-center mb-40">
            <div class="col-12 text-center">
                <h2 class="text-white">Contacta con nosotros</h2>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-xl-5 col-lg-6 col-md-8 col-sm-10 col-12">
                <div class="contact-cta-list">

                    @if(setting('page_phone'))
                    <a href="tel:+{{ setting('page_phone') }}" class="contact-cta-item">
                        <div class="contact-cta-icon"><i class="fas fa-phone"></i></div>
                        <span class="contact-cta-label">+{{ setting('page_phone') }}</span>
                    </a>
                    @endif

                    @if(setting('page_whatsapp'))
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', setting('page_whatsapp')) }}" target="_blank" class="contact-cta-item">
                        <div class="contact-cta-icon"><i class="fab fa-whatsapp"></i></div>
                        <span class="contact-cta-label">{{ setting('page_whatsapp') }}</span>
                    </a>
                    @endif

                    @if(setting('page_email'))
                    <a href="mailto:{{ setting('page_email') }}" class="contact-cta-item">
                        <div class="contact-cta-icon"><i class="fas fa-envelope"></i></div>
                        <span class="contact-cta-label">{{ setting('page_email') }}</span>
                    </a>
                    @endif

                    @if(setting('page_address'))
                    <div class="contact-cta-item">
                        <div class="contact-cta-icon"><i class="fas fa-location-dot"></i></div>
                        <span class="contact-cta-label">{{ setting('page_address') }}</span>
                    </div>
                    @endif

                    @if(setting('page_hour_weekend'))
                    <div class="contact-cta-item">
                        <div class="contact-cta-icon"><i class="fas fa-clock"></i></div>
                        <span class="contact-cta-label">
                            Lun–Vie: {{ setting('page_hour_weekend') }}
                            @if(setting('page_hour_weekends'))
                                &nbsp;·&nbsp; Sáb: {{ setting('page_hour_weekends') }}
                            @endif
                        </span>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</section>

@push('css')
    <link rel="stylesheet" href="{{ asset('pages/css/partials/sections/pages/contact.css') }}">
@endpush
