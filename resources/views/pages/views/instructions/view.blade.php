@extends('layouts.pages')

@section('content')

    {{-- Igual que el index, esta vista venía copiada de bundles/view: pintaba
         precio, duración y un $courses que nadie pasaba, así que
         /instructions/{slug} respondía 500. Reescrita sobre Instruction. --}}

    <main class="main-area fix">

        <section class="courses__breadcrumb-area">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="courses__breadcrumb-content">
                            @isset($instruction->categorie)
                                <span class="sub-title">{{ $instruction->categorie->title }}</span>
                            @endisset
                            <h3 class="title">{{ $instruction->title }}</h3>
                            {!! clean($instruction->short, 'content') !!}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="courses-details-area section-pb-120">
            <div class="container">
                <div class="row">
                    <div class="col-xl-9 col-lg-9">
                        <div class="courses__details-content">
                            {!! clean($instruction->description, 'content') !!}
                        </div>

                        <a href="{{ route('instructions') }}" class="theme-btn style-three mt-30">
                            Volver a instructivos
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </main>

@endsection
