@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">
            <div class="card w-100">
                <div class="card-body border-top">
                    <div class="d-flex no-block align-items-center mb-3">
                        <h5 class="mb-0">Detalle de categoría</h5>
                        <div class="ms-auto">
                            <a href="{{ route('manager.categories.courses') }}" class="btn btn-light btn-sm">
                                Volver
                            </a>
                            <a href="{{ route('manager.categories.courses.edit', $categorie->slack) }}" class="btn btn-primary btn-sm ms-1">
                                Editar
                            </a>
                        </div>
                    </div>

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
