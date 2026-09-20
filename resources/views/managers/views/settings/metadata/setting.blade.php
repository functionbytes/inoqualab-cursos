@extends('layouts.managers')


@section('content')
    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">
            <div class="card w-100">
                <form id="formMetadata" enctype="multipart/form-data" role="form"
                      data-urls='@php $__jsonInline1 = [
                          "update" => route("manager.settings.metadata.update"),
                          "store" => route("manager.settings.metadata.store"),
                          "get" => route("manager.settings.metadata.get", ":item"),
                          "delete" => route("manager.settings.metadata.delete", ":id"),
                          "dashboard" => route("manager.dashboard"),
                      ]; @endphp@json($__jsonInline1)'>
                    {{ csrf_field() }}

                    <input type="hidden" id="meta_description" name="meta_description" value="{{ setting('meta_description') }}">
                    <input type="hidden" id="slack" name="slack" value="{{ setting('meta_image') }}">
                    <input type="hidden" id="statuMetas" name="statuMetas" value="{{ $metadata }}">
                    <input type="hidden" id="statuEdit" name="statuEdit" value="true">
                    <input type="hidden" id="metadata" name="metadata">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Imagen</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para que puedas actualizar y modificar la foto de tu perfil es necesario actualizar para mantener tus datos al día.
                        </p>
                        <div class="dropzone dz-clickable" id="metadata">
                            <div class="fallback">
                                <input type="file" hidden name="metadata">
                            </div>
                        </div>
                        <label id="metadata-error" class="error d-none" for="metadata"></label>
                    </div>


                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Editar metadata</h5>
                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Configura los metadatos SEO por defecto del sitio: título, palabras clave y descripción.
                        </p>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Título</label>
                                    <input type="text" class="form-control" id="meta_title" name="meta_title" value="{{ setting('meta_title') }}" placeholder="Ingresar título">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Palabras clave</label>
                                    <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" value="{{ setting('meta_keywords') }}" >
                                </div>
                            </div>
                            <div class="col-12 mt-3">
                                <label class="control-label col-form-label">Descripción</label>
                                <div class="">
                                    <div id="descriptions">{!! clean(setting('meta_description'), 'content') !!}</div>
                                </div>
                                <label id="description-error" class="error d-none" for="description"></label>
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
<script src="{{ asset('managers/js/views/settings/metadata/setting.js') }}"></script>
@endpush
