
@if(isset($trusteds))

    <!-- Logo Section Start -->
    <div class="logo-section pt-130 rpt-100 pb-80 rpb-50">
        <div class="container">
            <div class="logo-inner">
                @foreach ($trusteds as $trusted)
                    <div class="logo-item wow fadeInUp delay-0-1s">
                        <a href="{{ $trusted->url != null ? $trusted->url : '#' }}">
                            @if(count($trusted->getMedia('thumbnail'))>0)
                                <img src="{{ $trusted->getfirstMedia('thumbnail')->getfullUrl() }}"
                                     class="js-img-fallback" data-fallback-src="{{ asset('/pages/images/trusted/default.png') }}">
                            @else
                                <img src="{{ asset('/pages/images/trusted/default.png') }}">
                            @endif
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif