@extends('layouts.pages')

@section('title', 'Inicio')

@section('content')

<section class="page-banner-area rel z-1 text-white text-center"
    style="background-image: url(/pages/images/banner.jpg);">
    <div class="container">
        <div class="banner-inner rpt-10">
            <h2 class="page-title wow fadeInUp delay-0-2s animated">Certificadores</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb wow fadeInUp delay-0-4s animated">
                    <li class="breadcrumb-item"><a href="{{ route('index') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Certificadores</li>
                </ol>
            </nav>
        </div>
    </div>
</section>

<section class="instructors-page ">
    <div class="container">
        <div class="row">
           @foreach ($certifiers as $certifier)
                <div class="col-lg-4 col-md-4 col-sm-6">
                    <a href="{{ route('certifiers.view',$certifier->slack) }}">
                        <div class="instructor-item wow fadeInUp delay-0-2s animated">
                            <div class="image">
                                @if($certifier->hasMedia('thumbnail'))
                                    <img src="{{ $certifier->getFirstMediaUrl('thumbnail') }}" alt="{{ $certifier->firstname . ' ' . $certifier->lastname }}" loading="lazy">
                                @else
                                    <img src="/pages/images/certifier/default.jpg" alt="{{ $certifier->firstname . ' ' . $certifier->lastname }}" loading="lazy">
                                @endif
                            </div>
                            <div class="member-description">
                                <h4>{{ $certifier->firstname . ' ' . $certifier->lastname }}</h4>
                                <span>{{ $certifier->profession }}</span>
                                <div class="ratting">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
           @endforeach
        </div>
    </div>
</section>


@endsection