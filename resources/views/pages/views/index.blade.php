@extends('layouts.pages')

@section('title', 'Inicio')

@push('css')
    <link rel="stylesheet" href="{{ url('/pages/css/cursos.css') }}">
@endpush

@section('content')


    @include ('pages.partials.sections.actions.action')

    @include ('pages.partials.sections.pages.calltoaction')

    @include ('pages.partials.sections.pages.populars')

    @include ('pages.partials.sections.pages.courses')

    @if (isset($bundles) && $bundles->isNotEmpty())
        <div class="cursos-page">
            <div class="catalog">
                <div class="cx-container">
                    @include('pages.partials.sections.bundles-strip')
                </div>
            </div>
        </div>
    @endif

    @if (isset($testimonials) && $testimonials->isNotEmpty())
        @include('pages.partials.sections.testimonies.testimonies')
    @endif

    @include ('pages.partials.sections.pages.home-faq')

@endsection
