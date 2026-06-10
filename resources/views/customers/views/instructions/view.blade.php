@extends('layouts.customers')

@section('title', 'Cursos')

@section('content')

    <div class="card bg-light-info shadow-none position-relative overflow-hidden">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-9">
                    <h4 class="fw-semibold mb-8">Instruccion</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a class="text-muted text-decoration-none"
                                                           href="{{ route('customers.instructions') }}">Inicio</a></li>
                            <li class="breadcrumb-item" aria-current="page">Instrucciones</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-3">
                    <div class="text-center mb-n5">
                        <img src="./images/breadcrumb/ChatBc.png" alt="" class="img-fluid mb-n4">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card rounded-2 overflow-hidden">

        <div class="card-body p-4">
            <span class="badge text-bg-light fs-2 rounded-4 py-1 px-2 lh-sm  mt-3">{{ $instruction->categorie->title }}</span>
            <h2 class="fs-9 fw-semibold mb-0">{{ $instruction->title }}.</h2>
        </div>
        <div class="card-body border-top p-4">
            {!! $instruction->description !!}
        </div>
    </div>

@endsection


