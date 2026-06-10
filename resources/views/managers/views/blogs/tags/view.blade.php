@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <div class="card-body border-top">
                    <div class="d-flex no-block align-items-center">
                        <h5 class="mb-0">Detalle de etiqueta</h5>
                    </div>
                    <p class="card-subtitle mb-3 mt-3">
                        Información de la etiqueta de blog registrada en el sistema.
                    </p>

                    <div class="row">

                        <div class="col-6">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Título</label>
                                <input type="text" class="form-control" value="{{ $tag->title }}" disabled>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Slug</label>
                                <input type="text" class="form-control" value="{{ $tag->slug }}" disabled>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Estado</label>
                                <input type="text" class="form-control" value="{{ $tag->available ? 'Publico' : 'Oculto' }}" disabled>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Fecha de creación</label>
                                <input type="text" class="form-control" value="{{ $tag->created_at ? date('Y-m-d', strtotime($tag->created_at)) : '-' }}" disabled>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </div>

@endsection
