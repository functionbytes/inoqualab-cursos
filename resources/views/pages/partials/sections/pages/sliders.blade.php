

<div class="rbt-slider-main-wrapper position-relative">
    <!-- Start Banner Area  -->
    <div class="swiper rbt-banner-activation rbt-slider-animation rbt-arrow-between">
        <div class="swiper-wrapper">
            
            @foreach ($sliders as $slider)
                @if ($slider->available == 1)

                        <div class="swiper-slide">
                            @if($slider->image!=null)
                                <div class="rbt-banner-area rbt-banner-6 variation-03 bg_image bg_image--17" data-gradient-overlay="5" style="background-image: url({{ asset('/pages/images/sliders/'.$slider->image) }});">
                            @else
                                <div class="rbt-banner-area rbt-banner-6 variation-03 bg_image bg_image--17" data-gradient-overlay="5" style="background-image: url({{ asset('/pages/images/sliders/'.$slider->image) }});">
                            @endif
                                <div class="wrapper w-100">
                                    <div class="container">
                                        <div class="row align-items-center">
                                            <div class="col-lg-12">
                                                <div class="inner text-center">
                                                    <div class="section-title">
                                                        <span class="subtitle bg-white-opacity d-inline-block">{{ $slider->subtitle }}</span>
                                                    </div>
                                                    <h1 class="title w-700">{{ $slider->title }}</h1>
                                                    @if($slider->detail)
                                                        <p class="title w-700">{{ $slider->detail }}</p>
                                                    @endif
                                                    @if($slider->url)
                                                        <div class="button-group mt--0">
                                                            <a class="rbt-btn btn-gradient rbt-marquee-btn" href="{{ $slider->url }}">
                                                                <span data-text="Más información">Más información</span>
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
            @endforeach

        </div>

        <div class="rbt-swiper-arrow rbt-arrow-left">
            <div class="custom-overfolow">
                <i class="rbt-icon fas fa-arrow-left"></i>
                <i class="rbt-icon-top fas fa-arrow-left"></i>
            </div>
        </div>

        <div class="rbt-swiper-arrow rbt-arrow-right">
            <div class="custom-overfolow">
                <i class="rbt-icon fas fa-arrow-right"></i>
                <i class="rbt-icon-top fas fa-arrow-right"></i>
            </div>
        </div>

    </div>

</div>
