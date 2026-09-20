<!-- Slider Section Start -->
<section class="slider-section bg-dark">
    <div class="main-slider">
        @foreach ($sliders as $slider)
        @if ($slider->available == 1)
            @if(count($slider->getMedia('thumbnail'))>0)
                <div class="slider-item" data-bg="{{ $slider->getfirstMedia('thumbnail')->getfullUrl() }}">
            @endif

            <div class="container">
                <div class="slider-content">
                    <span class="sub-title-three">{{ $slider->subtitle }}</span>
                    <h2>{{ $slider->title }}</h2>
                    @if($slider->detail)
                    <p class="title w-700">{{ $slider->detail }}</p>
                    @endif
                    @if($slider->url)
                    <div class="slider-btns">
                        <a href="{{ $slider->url }}" class="theme-btn style-four">Más información <i
                                class="fas fa-arrow-right"></i></a>
                    </div>
                    @endif

                </div>
            </div>
        </div>
        @endif
        @endforeach
    </div>
    <div class="pagingInfo">
        <span class="pagingStatus"></span>
        <span class="separator">/</span>
        <span class="pagingCount"></span>
    </div>
</section>
<!-- Slider Section End -->

@push('scripts')
    <script src="{{ asset('pages/js/common/apply-data-bg.js') }}"></script>
@endpush

