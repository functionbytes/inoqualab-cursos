@extends('layouts.pages')

@inject('finds', 'App\Models\Course\Course')

@section('title', "$bundle->title")

@section('head')

    @php
    $url = URL::current();
    @endphp

    <meta name="title" content="{{ $bundle->title }}">
    <meta name="description" content="{{ $bundle->short_detail }} ">
    <meta property="og:title" content="{{ $bundle->title }} ">
    <meta property="og:url" content="{{ $url }}">
    <meta property="og:description" content="{{ $bundle->short_detail }}">
    <meta property="og:image" content="{{ asset('images/course/' . $bundle->preview_image) }}">
    <meta itemprop="image" content="{{ asset('images/course/' . $bundle->preview_image) }}">
    <meta property="og:type" content="website">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ asset('images/course/' . $bundle->preview_image) }}">
    <meta property="twitter:title" content="{{ $bundle->title }} ">
    <meta property="twitter:description" content="{{ $bundle->short_detail }}">
    <meta name="twitter:site" content="{{ url()->full() }}" />

    <link rel="canonical" href="{{ url()->full() }}" />
    <meta name="robots" content="all">
    <meta name="keywords" content="">


@endsection

@section('content')

    <main class="main-area fix">

        <!-- breadcrumb-area -->
        <section class="courses__breadcrumb-area">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="courses__breadcrumb-content">
                            <h3 class="title">{{ $bundle->title }}</h3>
                            <p>{!! $bundle->description !!}</p>
                            <ul class="courses__item-meta list-wrap">
                                <li>
                                    <div class="rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <span class="rating-count">(5.0)</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- breadcrumb-area-end -->

        <section class="courses-details-area section-pb-120">
            <div class="container">
                <div class="row">
                    <div class="col-xl-8 col-lg-8">
                        <div class="bundles-courses">
                                <div class="row ">
                                    @foreach ($courses as $course)
                                        <div class="col-md-12">
                                                <div class="course-item style-two  wow fadeInUp delay-0-4s animated">
                                                    <div class="course-image">
                                                            <a href="{{ route('courses.view', $course->slack) }}" class="category">{{ $course->categorie->title }}</a>
                                                            <img src="{{ count($course->getMedia('thumbnail'))>0 ? $course->getfirstMedia('thumbnail')->getfullUrl() : asset('/pages/images/courses/default.jpg') }}"
                                                                 class="card-img-top rounded-0 object-fit-cover" alt="..." height="440"
                                                                 onerror="this.src='{{ asset('/pages/images/courses/default.jpg') }}'">
                                                    </div>
                                                    <div class="course-content">
                                                        <h4>
                                                            <a href="{{ route('courses.view', $course->slack ) }}">
                                                                {{ $course->title }}
                                                            </a>
                                                        </h4>
                                                        {!! Str::words( $course->short, 20, '...')  !!}

                                                        <div class="ratting-price">
                                                            <div class="ratting">
                                                                <i class="fas fa-star"></i>
                                                                <i class="fas fa-star"></i>
                                                                <i class="fas fa-star"></i>
                                                                <i class="fas fa-star"></i>
                                                                <i class="fas fa-star"></i>
                                                            </div>
                                                            @if ($course->payment == 1)
                                                                @php $instrOnSale = $course->promotion == 1 && $course->discount < $course->price; @endphp
                                                                @if ($instrOnSale)
                                                                    <div class="rbt-price">
                                                                        <span class="price">${{ number_format($course->discount, 0, ',', '.') }}</span>
                                                                        <span class="off-price">${{ number_format($course->price, 0, ',', '.') }}</span>
                                                                    </div>
                                                                @else
                                                                    <div class="rbt-price">
                                                                        <span class="price">${{ number_format($course->price, 0, ',', '.') }}</span>
                                                                    </div>
                                                                @endif
                                                            @elseif($course->payment == 0)
                                                                <span class="price">GRATIS</span>
                                                            @endif
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                    @endforeach
                                </div>
                        </div>

                    </div>
                    <div class="col-xl-4 col-lg-4">
                        <aside class="courses__details-sidebar">
                            <div class="event-widget">
                                <div class="thumb">
                                    @if(count($bundle->getMedia('thumbnail'))>0)
                                        <img src="{{ $bundle->getfirstMedia('thumbnail')->getfullUrl() }}"
                                             onerror="this.src='{{ asset('/pages/images/courses/default.jpg') }}'">
                                    @else
                                        <img src="{{ asset('/pages/images/courses/default.jpg') }}">
                                    @endif
                                    @if ($bundle->film!=null)
                                            <a href="{{ $bundle->film }}" class="popup-video"><i class="fas fa-play"></i></a>
                                    @endif
                                </div>
                                <div class="event-cost-wrap">
                                            <h4 class="price">${{ number_format($bundle->price, 0, ',', '.') }}</h4>
                                </div>

                                <div class="event-information-wrap ">
                                        <ul class="list-wrap">
                                            <li><i class="fas fa-stopwatch"></i>Periodo de caducidad <span>{{ $bundle->duration }}  {{ $bundle->duration == 1 ? 'Dia' : 'Dias' }}</span></li>
                                            <li><i class="fas fa-list"></i>Curso incluido <span>{{ $bundle->courses->count() }}</span></li>
                                        </ul>


                                        <div class="course-button-wrap">
                                         
                                            <a href="{{ route('checkout', ['bundle',$bundle->slack]) }}" class="btn">ADQUIRIR</a>

                                            <div class="coursedetails__footer">
                                                <p>Para más detalles</p>
                                                <a href="tel:{{ setting('whatsapp') }}"> <i class="fas fa-phone"></i> {{ setting('whatsapp') }}</a>
                                            </div>

                                        </div>

                                </div>
                            </div>

                        </aside>
                    </div>
                </div>
            </div>
        </section>


    </main>
@endsection
