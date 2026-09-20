@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formSlider" enctype="multipart/form-data" role="form"
                      data-urls='@php $__jsonInline1 = [
                          "update" => route("manager.sliders.update"),
                          "index" => route("manager.sliders"),
                          "thumbnails" => route("manager.sliders.thumbnails"),
                          "thumbnailsGet" => route("manager.sliders.thumbnails.get", ":item"),
                          "thumbnailDelete" => route("manager.sliders.thumbnails.delete", ":id"),
                      ]; @endphp@json($__jsonInline1)'>

                    {{ csrf_field() }}

                    <input type="hidden" id="id" name="id" value="{{ $slider->id }}">
                    <input type="hidden" id="slack" name="slack" value="{{ $slider->slack }}">
                    <input type="hidden" id="status" name="status" value="{{ $thumbnail }}">
                    <input type="hidden" id="edit" name="edit" value="true">
                    <input type="hidden" id="thumbnail" name="thumbnail">
                    <input type="hidden" id="position" name="position" value="{{ $slider->position }}">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Imagen</h5>
                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Sube la imagen que se mostrará en el banner.
                        </p>
                        <div class="dropzone dz-clickable dz-started" id="thumbnail">
                            <div class="fallback">
                                <input type="file" hidden name="file">
                            </div>
                        </div>
                        <label id="thumbnail-error" class="error d-none" for="thumbnail"></label>
                    </div>

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Editar banner</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Actualiza los datos del banner. Los cambios se guardarán al hacer clic en Guardar.
                        </p>

                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Titulo</label>
                                    <input type="text" class="form-control" id="title"  name="title" value="{{ $slider->title }}" placeholder="Ingresar titulo">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Subtitulo</label>
                                    <input type="text" class="form-control" id="subtitle"  name="subtitle" value="{{ $slider->subtitle }}" placeholder="Ingresar subtitulo">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Descripción</label>
                                    <input type="text" class="form-control" id="description"  name="description" value="{{ $slider->description }}" placeholder="Ingresar descripcion">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Link</label>
                                    <input type="text" class="form-control" id="url"  name="url" value="{{ $slider->url }}" placeholder="Ingresar link">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Ubicación</label>
                                    <div class="input-group">
                                        {!! Form::select('ubication', $ubications, $slider->ubication, ['class' => 'select2 form-control','id' => 'ubication']) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Estado</label>
                                    <div class="input-group">
                                        {!! Form::select('available', $availables, $slider->available, ['class' => 'select2 form-control','id' => 'available']) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                            <div class="border-top pt-1 mt-4">
                                <button type="submit" class="btn btn-info  px-4 waves-effect waves-light mt-2 w-100">
                                        Guardar
                                </button>
                            </div>
                        </div>

                        </div>

                    </div>

                </form>
            </div>

        </div>

    </div>

@endsection
@push('scripts')
<script src="{{ asset('managers/js/views/settings/sliders/edit.js') }}"></script>
@endpush
