

<!-- Coach Section Start -->
<section class="coach-section rel z-1 pt-120 rpt-90 pb-100 rpb-70 ">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-8 col-md-8">
                <div class="section-title text-center mb-40">
                    <h2>Explora Nuestros cursos y desarrolla tus habilidades con nosotros!</h2>
                </div>
            </div>
        </div>
        {{--  <ul class="coach-filter mb-35">
            <li data-filter="*" class="current">Todas</li>
            @foreach ($categories as $category)
                <li data-filter=".{{ $category->slug }}">{{ $category->title }}</li>
            @endforeach
        </ul>  --}}
        <div class="crs-grid">
            @foreach ($courses as $course)
                @include('pages.partials.components.course-card', ['course' => $course])
            @endforeach
        </div>
    </div>
</section>
<!-- Coach Section End -->
