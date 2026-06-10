@extends('layouts.pages')

@section('title', 'Inicio')

@section('content')

    <section class="page-banner-area rel z-1 text-white text-center" style="background-image: url(/pages/images/banner.jpg);">
        <div class="container">
            <div class="banner-inner rpt-10">
                <h2 class="page-title wow fadeInUp delay-0-2s animated" >Noticias</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb wow fadeInUp delay-0-4s animated" >
                        <li class="breadcrumb-item"><a href="{{ route('index') }}">Inicio</a></li>
                        <li class="breadcrumb-item active">Noticias</li>
                    </ol>
                </nav>
            </div>
        </div>
    </section>

    <section class="blog-standard-area py-130 rpt-95 rpb-100">
        <div class="container">
            <div class="row">
                <div class="col-lg-8" id="divBlogs">

                    @include('pages.partials.sections.blogs.items')

                </div>
                <div class="col-lg-4">

                    @include('pages.partials.sections.blogs.search')
                    @include('pages.partials.sections.blogs.categories')
                    @include('pages.partials.sections.blogs.recents')
                    @include('pages.partials.sections.blogs.tags')
                </div>
            </div>
        </div>
    </section>

@endsection
