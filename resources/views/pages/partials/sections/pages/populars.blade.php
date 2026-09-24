@if(isset($populars))

    <!-- Events Section Start -->
    <section class="events-section rel z-1 py-130 rpy-150 bg-lighter">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-9  col-lg-9 col-md-9 col-sm-12">
                    <div class="section-title text-center mb-55">
                        <span class="sub-title mb-25">Cursos destacados</span>
                        <h2>Descubre nuestros cursos y aprende de los mejores en la plataforma</h2>
                    </div>
                </div>
            </div>
            <div class="coach-slider crs-slider">
                @foreach ($populars as $course)
                    <div>
                        @include('pages.partials.components.course-card', ['course' => $course])
                    </div>
                @endforeach
            </div>
             </div>
        <span class="bg-text">destacados</span>
    </section>
    <!-- Events Section End -->


@endif