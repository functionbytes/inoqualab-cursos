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
            <div class="coach-slider">
                @foreach ($populars as $course)
                <div class="coach-item wow fadeInUp delay-0-2s">
                    <div class="coach-image">
                        <a href="{{ route('courses.view', [$course->slack]) }}" class="category">{{ $course->categorie->title }}</a>
                            @if(count($course->getMedia('thumbnail'))>0)
                                <img src="{{ $course->getFirstMedia('thumbnail')->getFullUrl() }}"
                                     class="card-img-top rounded-0 object-fit-cover" alt="{{ $course->title }}" height="440" loading="lazy"
                                     onerror="this.src='{{ asset('/pages/images/courses/default.jpg') }}'">
                            @else
                                <img src="{{ asset('/pages/images/courses/default.jpg') }}" class="card-img-top rounded-0 object-fit-cover" alt="{{ $course->title }}" height="440" loading="lazy">
                            @endif
                        </a>
                    </div>
                    <div class="coach-content">
                        <h4><a href="{{ route('courses.view', [$course->slack]) }}">{{ $course->title }}</a></h4>
                        <div class="ratting-price">
                            <div class="ratting">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            @if ($course->payment == 1)
                                @php $popOnSale = $course->promotion == 1 && $course->discount < $course->price; @endphp
                                @if ($popOnSale)
                                    <div class="rbt-price">
                                        <span class="price">{{ number_format($course->discount, 0, ',', '.') }}</span>
                                        <span class="off-price">${{ number_format($course->price, 0, ',', '.') }}</span>
                                    </div>
                                @else
                                    <div class="rbt-price">
                                        <span class="price">{{ number_format($course->price, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                            @elseif($course->payment == 0)
                                <span class="price">GRATIS</span>
                            @endif

                        </div>
                        <ul class="coach-footer">
                            <li><i class="fas fa-list"></i><span>{{ $course->lessons_count ?? count($course->lessons) }} Clases</span></li>
                            <li><i class="fas fa-folder"></i><span>{{ $course->chapters_count ?? count($course->chapters) }} Temas</span></li>
                        </ul>
                    </div>
                </div>
                @endforeach
            </div>
             </div>
        <span class="bg-text">destacados</span>
    </section>
    <!-- Events Section End -->


@endif