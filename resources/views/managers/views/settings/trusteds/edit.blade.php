@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Editar aliado'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">

            <form id="formTrusted" enctype="multipart/form-data" role="form"
                  data-urls='@php $__jsonInline1 = [
                      "update" => route("manager.trusteds.update"),
                      "index" => route("manager.trusteds"),
                      "thumbnails" => route("manager.trusteds.thumbnails"),
                      "thumbnailsGet" => route("manager.trusteds.thumbnails.get", ":item"),
                      "thumbnailDelete" => route("manager.trusteds.thumbnails.delete", ":id"),
                  ]; @endphp@json($__jsonInline1)'>

                {{ csrf_field() }}

                <input type="hidden" id="id" name="id" value="{{ $trusted->id }}">
                <input type="hidden" id="slack" name="slack" value="{{ $trusted->slack }}">
                <input type="hidden" id="status" name="status" value="{{ $thumbnail }}">
                <input type="hidden" id="edit" name="edit" value="true">
                <input type="hidden" id="thumbnail" name="thumbnail">

                <div class="card">

                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">Imagen</h6>
                        <p class="text-muted mb-3">
                            Sube el logo de la empresa aliada.
                        </p>
                        <div class="dropzone dz-clickable dz-started" id="thumbnail">
                            <div class="fallback">
                                <input type="file" hidden name="file">
                            </div>
                        </div>
                        <label id="thumbnail-error" class="error d-none" for="thumbnail"></label>
                    </div>

                    <hr class="my-0">

                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">Editar aliado</h6>
                        <p class="text-muted mb-3">
                            Actualiza los datos de la empresa aliada. Los cambios se guardarán al hacer clic en Guardar.
                        </p>

                        <div class="row g-3">

                            <div class="col-6">
                                <label for="cono1" class="form-label fw-semibold">Titulo</label>
                                <input type="text" class="form-control" id="title"  name="title" value="{{ $trusted->title }}" placeholder="Ingresar titulo">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Estado</label>
                                <div class="input-group">
                                    {!! Form::select('available', $availables, $trusted->available, ['class' => 'select2 form-control','id' => 'available']) !!}
                                </div>
                                <label id="available-error" class="error" for="available"></label>
                            </div>

                            <div class="col-12">
                                <label for="cono1" class="form-label fw-semibold">Link</label>
                                <input type="text" class="form-control" id="url"  name="url" value="{{ $trusted->url }}" placeholder="Ingresar la url">
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
                    <h6 class="mb-0 fw-bold">Sobre los aliados</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Los logos de empresas aliadas se muestran en el sitio público. El <strong>link</strong> es la URL a la que redirige el logo al hacer clic.</p>
                </div>
            </div>

        </div>

    </div>

@endsection



@push('scripts')
<script src="{{ asset('managers/js/views/settings/trusteds/edit.js') }}"></script>
@endpush
