@extends('layouts.pages')

@section('content')

    {{-- Esta vista era una copia sin adaptar de bundles/index: iteraba $bundles
         paginado, una variable que InstructionsController::index() nunca pasa
         (envía 'instructions' sin paginar), así que /instructions reventaba en
         cada visita. Reescrita sobre los datos reales del controller. --}}

    <section class="instructions-section wow fadeInUp delay-0-2s padding-top padding-bottom">
        <div class="container">

            <div class="section-title pb-50 text-center">
                <span class="sub-title mb-1">Centro de ayuda</span>
                <h2>Instructivos</h2>
            </div>

            @if ($instructions->isEmpty())
                <div class="row justify-content-center">
                    <div class="col-lg-6 text-center">
                        <p>Todavía no hay instructivos publicados.</p>
                    </div>
                </div>
            @else
                <div class="row">
                    @foreach ($instructions as $instruction)
                        <div class="col-lg-6">
                            <div class="instruction-item wow fadeInUp delay-0-2s mb-30">
                                @isset($instruction->categorie)
                                    <span class="sub-title">{{ $instruction->categorie->title }}</span>
                                @endisset

                                <h4>
                                    <a href="{{ route('instructions.view', $instruction->slug) }}">
                                        {{ $instruction->title }}
                                    </a>
                                </h4>

                                {!! clean($instruction->short, 'content') !!}

                                <a href="{{ route('instructions.view', $instruction->slug) }}" class="theme-btn style-three mt-15">
                                    Leer instructivo
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </section>

@endsection
