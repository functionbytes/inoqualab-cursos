@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Detalle de categoría'])
@endsection
@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">
            <div class="card w-100">
                <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold">Detalle de categoría</h6>
                    <div>
                        <a href="{{ route('manager.categories.courses') }}" class="btn btn-light btn-sm">
                            Volver
                        </a>
                        <a href="{{ route('manager.categories.courses.edit', $categorie->slack) }}" class="btn btn-primary btn-sm ms-1">
                            Editar
                        </a>
                    </div>
                </div>
                <div class="card-body">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Título</label>
                                <p class="form-control-plaintext border rounded px-3 py-2 bg-light">{{ $categorie->title }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Slug</label>
                                <p class="form-control-plaintext border rounded px-3 py-2 bg-light">{{ $categorie->slug }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
