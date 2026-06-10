<section class="contact-cta-section py-80 rpb-60" style="background:#0d1b2e;">
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
<style>
.contact-cta-list { display: flex; flex-direction: column; gap: 10px; }
.contact-cta-item {
    display: flex;
    align-items: center;
    gap: 16px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 12px;
    padding: 14px 20px;
    text-decoration: none;
    cursor: default;
    transition: background 0.2s, border-color 0.2s;
}
a.contact-cta-item { cursor: pointer; }
a.contact-cta-item:hover {
    background: rgba(0,139,205,0.15);
    border-color: rgba(0,139,205,0.4);
}
.contact-cta-icon {
    width: 46px; height: 46px; min-width: 46px;
    border-radius: 10px;
    background: #008bcd;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; color: #fff;
}
.contact-cta-label {
    color: #e0e8f0;
    font-size: 15px;
    font-weight: 500;
    line-height: 1.3;
}
</style>
@endpush
