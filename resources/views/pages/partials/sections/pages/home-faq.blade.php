@if (isset($homeFaqs) && $homeFaqs->isNotEmpty())
<section class="iq-home-faq">
    <div class="container">
        <div class="row g-5 align-items-start">
            <div class="col-xl-5">
                <div class="iq-home-faq__intro">
                    <span class="sub-title">Preguntas frecuentes</span>
                    <h2>Resolvemos tus dudas antes de empezar</h2>
                    <p class="desc">Certificados, pagos, horarios y metodología: lo que más nos preguntan quienes van a tomar un curso por primera vez.</p>
                    <div class="iq-home-faq__cta">
                        <a href="{{ route('faqs') }}" class="iq-home-faq__link">Ver todas las preguntas <i class="fas fa-arrow-right"></i></a>
                        @if(setting('page_whatsapp'))
                            <a href="https://wa.me/{{ preg_replace('/\D/', '', setting('page_whatsapp')) }}" target="_blank" rel="noopener" class="iq-home-faq__wa">¿Tu duda no está aquí? Escríbenos</a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-xl-7">
                <div class="iq-home-faq__accordion" id="homeFaqAccordion">
                    @foreach($homeFaqs as $index => $faq)
                        <div class="iq-faq-item">
                            <a class="{{ $index === 0 ? '' : 'collapsed' }} iq-faq-item-header"
                               id="homeFaqHeading{{ $faq->id }}"
                               data-toggle="collapse"
                               data-target="#homeFaqCollapse{{ $faq->id }}"
                               aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                               aria-controls="homeFaqCollapse{{ $faq->id }}">
                                {{ $faq->title }}
                                <span class="toggle-btn"></span>
                            </a>
                            <div id="homeFaqCollapse{{ $faq->id }}"
                                 class="collapse {{ $index === 0 ? 'show' : '' }}"
                                 aria-labelledby="homeFaqHeading{{ $faq->id }}"
                                 data-parent="#homeFaqAccordion">
                                <div class="iq-faq-item-body">{!! clean($faq->description, 'content') !!}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        $('#homeFaqAccordion .iq-faq-item-header').on('click', function() {
            $('#homeFaqAccordion .iq-faq-item-header').addClass('collapsed');
            $('#homeFaqAccordion .collapse').removeClass('show');

            if ($(this).hasClass('collapsed')) {
                $(this).removeClass('collapsed');
                $($(this).attr('data-target')).addClass('show');
            }
        });
    });
</script>
@endpush
@endif
