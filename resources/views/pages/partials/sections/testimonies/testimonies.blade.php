

<!-- Testimonials Section Start -->
<section class="testimonials-section bg-white rel z-1 py-130 rpy-100">
    <div class="container">
        <div class="row align-items-center justify-content-between">
            <div class="col-lg-5">
                <div class="testimonial-left-content rmb-65 wow fadeInLeft delay-0-2s">
                    <div class="section-title">
                        <span class="sub-title mb-15">Testimonios</span>
                        <h2>Los clientes satisfechos dicen</h2>
                    </div>
                    <p>Descubre cómo nuestros productos/servicios han transformado las vidas de personas como tú. ¡Sumérgete en las historias de éxito y únete a nuestra comunidad de clientes satisfechos!</p>
                    <h4 class="partner-title mt-25 mb-15">Tenemos más de  <span>{{$users}}+</span> estudiantes</h4>
                    <div class="partner-iamges-wrap">
                        <img src="/pages/images/testimonials/partner1.jpg" alt="Partner">
                        <img src="/pages/images/testimonials/partner2.jpg" alt="Partner">
                        <img src="/pages/images/testimonials/partner3.jpg" alt="Partner">
                        <img src="/pages/images/testimonials/partner4.jpg" alt="Partner">
                        <img src="/pages/images/testimonials/partner5.jpg" alt="Partner">
                        <img src="/pages/images/testimonials/partner6.jpg" alt="Partner">
                        <span class="plus">+</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="testimonial-wrap wow fadeInRight delay-0-2s">
                    @foreach ($testimonies as $testimony)
                        <div class="testimonial-item">
                            <div class="testimonial-author">
                                @if($testimony->image != null)
                                    <img  src="/pages/images/testimonies/{{ $testimony->image }}" alt="Video Images">
                                @else
                                    <img src="/pages/images/testimonies/default.jpg" alt="Clint Images">
                                @endif

                            </div>

                            <div class="testimonial-content">
                                <div class="designation">
                                    <h4>{{ $testimony->firstname }} {{ $testimony->lastname }}</h4>
                                    <span>Estudiante</span>
                                </div>
                                <p>{!! $testimony->description !!}</p>
                                <div class="ratting">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Testimonials Section End -->

<!-- Testimonials Section End -->
