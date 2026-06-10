@extends('layouts.pages')

@section('title', 'Inicio')

@section('content')
<!-- Course Left Start -->
<form action="{{ Request::fullUrl() }}" method="GET">
<section class="course-left-area py-70">
    <div class="container">
        <div class="row large-gap">
         <div class="col-lg-12">
                <div class="bundle-grids">
                        <div class="shop-shorter mb-40 wow fadeInUp delay-0-2s">

                            <div class="sort-text">
                                <span>Mostrar {{ $bundles->firstItem() }}-{{ $bundles->lastItem() }} de {{ $bundles->total() }} resultados</span>
                            </div>
                            <ul class="grid-list">
                                <li><a href="#"><i class="fas fa-list-ul"></i></a></li>
                                <li><a href="#" class="active"><i class="fas fa-border-all"></i></a></li>
                            </ul>
                        </div>

                    <div class="bundle-items">
                        <div class="row">
                            @foreach ($bundles as $bundle)
                                <div class="col-lg-6 col-sm-12 item  ">
                                    <div class="bundle-item wow fadeInUp delay-0-2s">
                                                <div class="bundle-head d-flex justify-content-between align-items-center flex-wrap">
                                                    <div class="bundle-title">
                                                        <a href="{{ route('bundles.view', $bundle->slack) }}">
                                                            <div class="title d-flex align-items-center g-12">
                                                                <h4 class="name"> {{ $bundle->title }}</h4>
                                                            </div>
                                                            <p class="info">{{ $bundle->courses->count() }} Coursos</p>
                                                        </a>
                                                    </div>
                                                    <div class="bundle-price">
                                                        <p class="price text-dark">${{ number_format($bundle->price) }}</p>
                                                    </div>
                                                </div>
                                                <div class="bundle-body ">
                                                    <ul>
                                                        @foreach ($bundle->courses as $course)
                                                        <li>
                                                            <div class="sbundle-item">
                                                                <div class="sbundle-title">
                                                                <a  href="{{ route('courses.view', $course->slack) }}" target="_blank">
                                                                    <div class="content">
                                                                        <div class="img">
                                                                            <img loading="lazy" src="{{ count($course->getMedia('thumbnail'))>0 ? $course->getfirstMedia('thumbnail')->getfullUrl() : asset('/pages/images/courses/default.jpg') }}" alt=""
                                                                 onerror="this.src='{{ asset('/pages/images/courses/default.jpg') }}'">
                                                                        </div>
                                                                        <h3 class="fw-400 title">{{ $course->title }}</h3>
                                                                    </div>
                                                                </a>
                                                                </div>

                                                                <div class="sbundle-price">
                                                                    <div class="price fw-400 text-16px text-muted">${{ number_format($course->price) }}</div>
                                                                </div>
                                                            </div>
                                                        </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                <div class="bundle-footer ">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <a href="{{ route('bundles.view', $bundle->slack) }}"  class="bundle-foot">Detalle paquete</a>
                                                        </div>
                                                        <div class="col-6">
                                                            <a href="{{ route('checkout', ['bundle',$bundle->slack]) }}" class="bundle-foot">${{ number_format($bundle->price) }} Comprar ahora</a>
                                                        </div>
                                                    </div>
                                                </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="row justify-content-center">
                            <ul class="pagination flex-wrap mt-20">
                                <nav>
                                    {{ $bundles->appends(request()->input())->links() }}
                                </nav>

                            </ul>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
      
        </div>
    </div>
</section></form>
@endsection
