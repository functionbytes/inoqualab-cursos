@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Detalle del blog'])
@endsection
@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <div class="card-header border-bottom">
                    <h6 class="mb-1 fw-bold">Detalle del blog</h6>
                    <p class="text-muted small mb-0">
                        Información del blog registrado en el sistema.
                    </p>
                </div>

                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-6">
                            <label class="form-label fw-semibold">Título</label>
                            <input type="text" class="form-control" value="{{ $blog->title }}" disabled>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold">Slug</label>
                            <input type="text" class="form-control" value="{{ $blog->slug }}" disabled>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold">Categoría</label>
                            <input type="text" class="form-control" value="{{ optional($blog->categorie)->title ?? '-' }}" disabled>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold">Estado</label>
                            <input type="text" class="form-control" value="{{ $blog->available ? 'Publico' : 'Oculto' }}" disabled>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold">Fecha de publicación</label>
                            <input type="text" class="form-control" value="{{ $blog->date_at ? date('Y-m-d', strtotime($blog->date_at)) : '-' }}" disabled>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold">Etiquetas</label>
                            <input type="text" class="form-control" value="{{ $blog->tags->pluck('title')->join(', ') ?: '-' }}" disabled>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Descripción</label>
                            <textarea class="form-control" rows="3" disabled>{{ $blog->description }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Contenido</label>
                            <div class="border rounded p-3 bg-light">
                                {!! clean($blog->content, 'content') !!}
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </div>

@endsection
