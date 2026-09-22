@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Detalle del blog'])
@endsection
@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <div class="card-body border-top">
                    <div class="d-flex no-block align-items-center">
                        <h5 class="mb-0">Detalle del blog</h5>
                    </div>
                    <p class="card-subtitle mb-3 mt-3">
                        Información del blog registrado en el sistema.
                    </p>

                    <div class="row">

                        <div class="col-6">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Título</label>
                                <input type="text" class="form-control" value="{{ $blog->title }}" disabled>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Slug</label>
                                <input type="text" class="form-control" value="{{ $blog->slug }}" disabled>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Categoría</label>
                                <input type="text" class="form-control" value="{{ optional($blog->categorie)->title ?? '-' }}" disabled>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Estado</label>
                                <input type="text" class="form-control" value="{{ $blog->available ? 'Publico' : 'Oculto' }}" disabled>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Fecha de publicación</label>
                                <input type="text" class="form-control" value="{{ $blog->date_at ? date('Y-m-d', strtotime($blog->date_at)) : '-' }}" disabled>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Etiquetas</label>
                                <input type="text" class="form-control" value="{{ $blog->tags->pluck('title')->join(', ') ?: '-' }}" disabled>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Descripción</label>
                                <textarea class="form-control" rows="3" disabled>{{ $blog->description }}</textarea>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Contenido</label>
                                <div class="border rounded p-3 bg-light">
                                    {!! clean($blog->content, 'content') !!}
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </div>

@endsection
