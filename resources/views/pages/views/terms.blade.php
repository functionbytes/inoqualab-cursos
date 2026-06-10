@extends('layouts.pages')
@section('title', 'Términos y Condiciones')
@section('content')

<section class="page-banner-area rel z-1 text-white text-center"
    style="background-image: url(/pages/images/banner.jpg);">
    <div class="container">
        <div class="banner-inner rpt-10">
            <h2 class="page-title wow fadeInUp delay-0-2s animated">Términos y Condiciones</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb wow fadeInUp delay-0-4s animated">
                    <li class="breadcrumb-item"><a href="{{ route('index') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Términos y Condiciones</li>
                </ol>
            </nav>
        </div>
    </div>
</section>

<section class="term-details-area padding-top padding-bottom">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="term-details-wrap">
                    <div class="term-content-wrap">
                        <div class="term-content">
                            {!! setting("page_term") !!}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

