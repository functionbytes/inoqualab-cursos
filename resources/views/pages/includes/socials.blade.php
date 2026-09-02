@if(setting('page_phone'))
    <a href="tel:+{{ setting('page_phone') }}" class="atl-float-btn atl-float-call" aria-label="Llamar a {{ setting('page_title') }}">
        <i class="fas fa-phone" aria-hidden="true"></i>
        <span>Llamar {{ setting('page_phone') }}</span>
    </a>
@endif

@if(setting('page_whatsapp'))
    <a href="https://wa.me/{{ preg_replace('/\D/', '', setting('page_whatsapp')) }}?text=Hola%2C+quiero+m%C3%A1s+informaci%C3%B3n+sobre+sus+cursos."
       target="_blank" rel="noopener" class="atl-float-btn atl-float-text" aria-label="Escribir a {{ setting('page_title') }} por WhatsApp">
        <i class="fab fa-whatsapp" aria-hidden="true"></i>
        <span>WhatsApp {{ setting('page_whatsapp') }}</span>
    </a>
@endif
