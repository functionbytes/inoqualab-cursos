@if(isset($blogs))

    <!-- Blog Section Start -->
    <section class="blog-section pt-60 rpt-90">
        <div class="container">
            <div class="section-title text-center mb-55">
                <span class="sub-title mb-25">Obtenga todas las actualizaciones</span>
                <h2>Últimas noticias y blogs</h2>
            </div>
            <div class="row">

                @foreach($blogs as $blog)
                <div class="col-lg-4 col-md-6">
                    <div class="blog-item style-two wow fadeInUp delay-0-2s">
                        <div class="blog-image">
                            @if($blog->image!=null)
                                <img src="{{ asset('/pages/images/blog/'.$blog->image) }}" alt="image">
                            @else
                                <img src="{{ asset('/pages/images/blog/default.jpg') }}" alt="image">
                            @endif
                            <span class="date text-uppercase">{{ $blog->categorie->title }}</span>
                        </div>
                        <div class="blog-content">
                            <div class="content">
                                <ul class="blog-meta">
                                    <li><i class="far fa-user"></i> <a href="">By Administrador</a></li>
                                    <li><i class="far fa-comments"></i> <a href="">Comments (5)</a></li>
                                </ul>
                                <h4><a href="{{ route('blogs.view',$blog->slug) }}">{{ strip_tags($blog->title) }}</a></h4>
                                <a href="{{ route('blogs.view',$blog->slug) }}" class="read-more">Leer más<i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    <!-- Blog Section End -->
    @endif










