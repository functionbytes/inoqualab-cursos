


@if(setting('cellphone_enable') == 'true' )
    <a href="tel:setting('cellphone')" target="_blank" class="bt-support-now theme-btns"><i class="fa fa-phone"></i><span>Llamar</span></a>
@endif
@if(setting('whatsapp_enable') == 'true' )
<a href="https://api.whatsapp.com/send?phone={{ setting('page_phone') }}" target="_blank" class="bt-buy-now theme-btns"><i class="fab fa-whatsapp"></i><span>Whatsapp</span></a>
@endif