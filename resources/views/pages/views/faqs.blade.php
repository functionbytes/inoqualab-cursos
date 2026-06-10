@extends('layouts.pages')

@section('title', 'Preguntar Frecuentes')

@section('content')



    <!-- FAQ's Section Start -->
    <section class="faqs-section  wow fadeInUp delay-0-2s padding-top padding-bottom">
        <div class="container">
            <div class="section-title pb-50 text-center">
                <span class="sub-title mb-1">¿Tiene alguna pregunta?</span>
                <h2>Preguntas frecuentes</h2>
            </div>

            <div class="tab-content faq-accordion">
                <div class="row">
                    <div class="col-lg-12">
                        <div  id="faqAccordion">
                            @php
                                $count = 0
                            @endphp

                            @foreach($faqs as $faq)
                                @php
                                    $count++
                                @endphp

                                <div class="card">
                                    <a class="collapsed card-header" 
                                       id="heading{{ $faq->id }}"
                                       data-toggle="collapse" 
                                       data-target="#collapse{{ $faq->id }}" 
                                       aria-expanded="false" 
                                       aria-controls="collapse{{ $faq->id }}">
                                        {{ $faq->title }}
                                        <span class="toggle-btn"></span>
                                    </a>
                                    <div id="collapse{{ $faq->id }}" 
                                         class="collapse" 
                                         aria-labelledby="heading{{ $faq->id }}"
                                         data-parent="#faqAccordion">
                                        <div class="card-body">
                                            <p>{!! $faq->description !!}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- FAQ's Section End -->



@endsection





@push('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        // Mostrar el primer elemento del acordeón por defecto
        $('#faqAccordion .card:first-child .card-header').removeClass('collapsed');
        $('#faqAccordion .card:first-child .collapse').addClass('show');
        
        // Manejar el evento click en los headers del acordeón
        $('.card-header').on('click', function() {
            $('.card-header').addClass('collapsed');
            $('.collapse').removeClass('show');
            
            if ($(this).hasClass('collapsed')) {
                $(this).removeClass('collapsed');
                $($(this).attr('data-target')).addClass('show');
            }
        });
    });
</script>

@endpush

