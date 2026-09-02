{{--
    Carrusel de testimonios del home, calcado del componente "Our Testimonials"
    de la plantilla Finsta (avatar + rating + cita + contador numérico grande).

    Dos adaptaciones respecto al original: sin foto real de la persona (no hay
    columna de imagen ni fotos reales de estudiantes/empresas) se quitó el
    círculo de avatar por completo -- el nombre y el rol quedan solos, sin el
    icono de sector/rol que traía el testimonio; y el pie de sección mantiene
    el mensaje propio de la plataforma en vez del rating de Google del
    original (no hay integración con Google Reviews, así que no hay una
    cifra real que mostrar ahí sin inventarla).
--}}
<!-- Our Testimonials Section Start -->
<section class="our-testimonials">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-7 col-lg-8 col-md-10">
                <div class="section-title text-center mb-55">
                    <span class="sub-title mb-25">Testimonios</span>
                    <h2>Lo que dicen nuestros estudiantes</h2>
                    <p>Empresas y estudiantes de diferentes sectores confían en nuestra formación para fortalecer sus procesos de calidad e inocuidad.</p>
                </div>
            </div>
        </div>

        <div class="testimonial-slider">
            @foreach ($testimonials as $testimonial)
                <div class="testimonial-item">
                    <div class="testimonial-item-author">
                        <div class="testimonial-author-content">
                            <h2>{{ $testimonial->firstname }} {{ $testimonial->lastname }}</h2>
                            @if($testimonial->role)
                                <p>{{ $testimonial->role }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="testimonial-item-rating">
                        @for ($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star{{ $i > $testimonial->rating ? ' star-empty' : '' }}"></i>
                        @endfor
                    </div>
                    <div class="testimonial-item-content">
                        {!! clean($testimonial->description, 'content') !!}
                    </div>

                    @if($testimonial->counter_value)
                        <div class="testimonial-item-counter">
                            <h2>{{ $testimonial->counter_value }}<sup>{{ $testimonial->counter_suffix }}</sup></h2>
                            @if($testimonial->benefit)
                                <p>{{ $testimonial->benefit }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
<!-- Our Testimonials Section End -->
