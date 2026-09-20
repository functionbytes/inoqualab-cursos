@extends('layouts.pages')

@section('title', 'Inicio')

@section('content')

    <section class="page-banner-area page-banner-area--default rel z-1 text-white text-center">
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

                    @isset($categorie)
                        <h3 class="mb-4">Categoría: {{ $categorie->title }}</h3>
                    @endisset

                    @isset($tag)
                        <h3 class="mb-4">Etiqueta: {{ $tag->title }}</h3>
                    @endisset

                    @if (!empty($search))
                        <p class="mb-4">
                            {{ $blogs->total() }} {{ $blogs->total() == 1 ? 'resultado' : 'resultados' }}
                            para <strong>{{ $search }}</strong>.
                            <a href="{{ route('blogs') }}">Ver todos</a>
                        </p>
                    @endif

                    @include('pages.partials.sections.blogs.items')

                    {{-- El listado se paginaba en el controller pero la vista nunca
                         pintaba los enlaces: al pasar de 15 posts no había forma de
                         llegar a la página 2. --}}
                    @if ($blogs->hasPages())
                        <div class="pagination-wrap mt-40">
                            {{-- bootstrap-4 explícito: el template público carga
                                 bootstrap-4.5.3, y el paginador por defecto de
                                 Laravel 12 es el de Tailwind. --}}
                            {{ $blogs->links('pagination::bootstrap-4') }}
                        </div>
                    @endif

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
