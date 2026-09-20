@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formDocuments" enctype="multipart/form-data" role="form"
                      data-urls='@php $__jsonInline1 = [
                          "store" => route("manager.documents.store"),
                          "index" => route("manager.documents"),
                          "files" => route("manager.documents.files"),
                          "filesDelete" => route("manager.documents.files.delete", ":id"),
                      ]; @endphp@json($__jsonInline1)'>

                    {{ csrf_field() }}

                    <input type="hidden" id="description" name="description" value="">
                    <input type="hidden" id="id" name="id" value="">
                    <input type="hidden" id="slack" name="slack" value="">
                    <input type="hidden" id="status" name="status" value="false">
                    <input type="hidden" id="edit" name="edit" value="true">
                    <input type="hidden" id="files" name="files">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Documento</h5>
                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Adjunta el archivo del documento. Se aceptan formatos PDF, JPG o PNG.
                        </p>
                        <div class="dropzone dz-clickable" id="files">
                            <div class="fallback">
                                <input type="file" hidden name="file">
                            </div>
                        </div>
                        <label id="files-error" class="error d-none" for="files"></label>
                    </div>

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Crear documento</h5>
                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Completa los datos del documento. Los campos marcados como obligatorios deben diligenciarse para poder guardarlo.
                        </p>
                        <div class="row">

                            <div class="col-12">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Titulo</label>
                                        <input type="text" class="form-control" id="title"  name="title" value="" placeholder="Ingresar titulo">
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
<script src="{{ asset('managers/js/views/settings/documents/create.js') }}"></script>
@endpush
