@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">
            
            <div class="card w-100">

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

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Imagen</h5>
                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Sube el logo de la empresa aliada.
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
                            <h5 class="mb-0">Editar aliado</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Actualiza los datos de la empresa aliada. Los cambios se guardarán al hacer clic en Guardar.
                        </p>

                        <div class="row">

                            <div class="col-6">
                                <div class="mb-3">
                                    <div class="mb-3">
                                        <label for="cono1" class="control-label col-form-label">Titulo</label>
                                        <input type="text" class="form-control" id="title"  name="title" value="{{ $trusted->title }}" placeholder="Ingresar titulo">
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Estado</label>
                                    <div class="input-group">
                                        {!! Form::select('available', $availables, $trusted->available, ['class' => 'select2 form-control','id' => 'available']) !!}
                                    </div>
                                    <label id="available-error" class="error" for="available"></label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <div class="mb-3">
                                        <label for="cono1" class="control-label col-form-label">Link</label>
                                        <input type="text" class="form-control" id="url"  name="url" value="{{ $trusted->url }}" placeholder="Ingresar la url">
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
<script src="{{ asset('managers/js/views/settings/trusteds/edit.js') }}"></script>
@endpush
