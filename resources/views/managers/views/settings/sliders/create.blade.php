@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Crear noticia'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">

            <form id="formSlider" enctype="multipart/form-data" role="form"
                  data-urls='@php $__jsonInline1 = [
                      "store" => route("manager.sliders.store"),
                      "index" => route("manager.sliders"),
                      "thumbnails" => route("manager.sliders.thumbnails"),
                      "thumbnailDelete" => route("manager.sliders.thumbnails.delete", ":id"),
                  ]; @endphp@json($__jsonInline1)'>

                {{ csrf_field() }}

                <input type="hidden" id="id" name="id" value="">
                <input type="hidden" id="slack" name="slack" value="">
                <input type="hidden" id="status" name="status" value="true">
                <input type="hidden" id="edit" name="edit" value="true">
                <input type="hidden" id="thumbnail" name="thumbnail">
                <input type="hidden" id="position" name="position" value="{{ $position }}">

                <div class="card">

                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">Imagen</h6>
                        <p class="text-muted mb-3">
                            Sube la imagen que se mostrará en el banner.
                        </p>
                        <div class="dropzone dz-clickable" id="thumbnail">
                            <div class="fallback">
                                <input type="file" hidden name="file">
                            </div>
                        </div>
                        <label id="thumbnail-error" class="error d-none" for="thumbnail"></label>
                    </div>

                    <hr class="my-0">

                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">Crear noticia</h6>
                        <p class="text-muted mb-3">
                            Completa los datos del banner. Los campos marcados como obligatorios deben diligenciarse para poder guardarlo.
                        </p>

                        <div class="row g-3">

                            <div class="col-6">
                                <label class="form-label fw-semibold">Titulo</label>
                                <input type="text" class="form-control" id="title"  name="title" value="" placeholder="Ingresar titulo">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Subtitulo</label>
                                <input type="text" class="form-control" id="subtitle"  name="subtitle" value="" placeholder="Ingresar subtitulo">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Descripción</label>
                                <input type="text" class="form-control" id="description"  name="description" value="" placeholder="Ingresar descripcion">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Link</label>
                                <input type="text" class="form-control" id="url"  name="url" value="" placeholder="Ingresar link">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Ubicación</label>
                                <div class="input-group">
                                    {!! Form::select('ubication', $ubications, null , ['class' => 'select2 form-control','id' => 'ubication']) !!}
                                </div>
                                <label id="ubication-error" class="error d-none" for="ubication"></label>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Estado</label>
                                <div class="input-group">
                                    {!! Form::select('available', $availables, null , ['class' => 'select2 form-control','id' => 'available']) !!}
                                </div>
                                <label id="available-error" class="error d-none" for="available"></label>
                            </div>

                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100">
                            Guardar
                        </button>
                    </div>

                </div>
            </form>
        </div>

        {{-- Columna derecha: sidebar informativo --}}
        <div class="col-lg-4">

            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Sobre los banners</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">La <strong>ubicación</strong> determina en qué sección del sitio público aparece este banner.</p>
                </div>
            </div>

        </div>

    </div>

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/settings/sliders/create.js') }}"></script>
@endpush
