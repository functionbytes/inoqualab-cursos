@extends('layouts.pages')

@section('title', 'Preguntas frecuentes')

@push('css')
    <link rel="stylesheet" href="{{ url('/pages/css/storefront.css') }}?v={{ @filemtime(public_path('pages/css/storefront.css')) ?: '1' }}">
@endpush

@section('content')

<div class="band">
    <div class="container">
        <div class="crumb">
            <a href="{{ route('index') }}">INICIO</a>
            <span class="sep">/</span>
            <span class="cur">PREGUNTAS FRECUENTES</span>
        </div>
        <h1>Preguntas frecuentes</h1>
        <p class="lede">Resolvemos las dudas más comunes sobre nuestros cursos y la plataforma.</p>
    </div>
</div>

<section class="faqs-section padding-top padding-bottom">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                @foreach($categories as $categorie)
                    <div class="iq-faq-category">
                        <h2 class="iq-faq-category-title">{{ $categorie->title }}</h2>

                        @foreach($categorie->faqs as $index => $faq)
                            <div class="iq-faq-item">
                                <a class="{{ $index === 0 && $loop->parent->first ? '' : 'collapsed' }} iq-faq-item-header"
                                   id="faqHeading{{ $faq->id }}"
                                   data-toggle="collapse"
                                   data-target="#faqCollapse{{ $faq->id }}"
                                   aria-expanded="{{ $index === 0 && $loop->parent->first ? 'true' : 'false' }}"
                                   aria-controls="faqCollapse{{ $faq->id }}">
                                    {{ $faq->title }}
                                    <span class="toggle-btn"></span>
                                </a>
                                <div id="faqCollapse{{ $faq->id }}"
                                     class="collapse {{ $index === 0 && $loop->parent->first ? 'show' : '' }}"
                                     aria-labelledby="faqHeading{{ $faq->id }}"
                                     data-parent="#faqAccordion">
                                    <div class="iq-faq-item-body">{!! clean($faq->description, 'content') !!}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>

            <div class="col-lg-4">
                @if($categories->count() > 1)
                    <div class="iq-faq-sidebar-box">
                        <h3 class="iq-faq-sidebar-title">Categorías</h3>
                        <ul class="iq-faq-sidebar-list">
                            @foreach($categories as $categorie)
                                <li><a href="#cat-{{ $categorie->id }}">{{ $categorie->title }} <i class="fas fa-arrow-up-right-from-square"></i></a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="iq-faq-cta-box">
                    <div>
                        <h2>¿No encuentras tu respuesta?</h2>
                        <p>Nuestro equipo te orienta sobre tus cursos y el uso de la plataforma.</p>
                        @if(setting('page_whatsapp'))
                            <a href="https://wa.me/{{ preg_replace('/\D/', '', setting('page_whatsapp')) }}" target="_blank" rel="noopener" class="btn-cta">Escríbenos <i class="fab fa-whatsapp"></i></a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
    <script src="{{ asset('pages/js/views/faqs.js') }}"></script>
@endpush

@endsection
