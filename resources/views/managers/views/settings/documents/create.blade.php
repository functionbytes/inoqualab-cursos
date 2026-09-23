@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Crear documento'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">

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

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Documento</h6>
                        <p class="text-muted small mb-0">
                            Adjunta el archivo del documento. Se aceptan formatos PDF, JPG o PNG.
                        </p>
                        </div>

                        <div class="card-body">
                            <div class="dropzone dz-clickable" id="files">
                            <div class="fallback">
                                <input type="file" hidden name="file">
                            </div>
                        </div>
                        <label id="files-error" class="error d-none" for="files"></label>
                    </div>

                    <hr class="my-0">

                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">Crear documento</h6>
                        <p class="text-muted mb-3">
                            Completa los datos del documento. Los campos marcados como obligatorios deben diligenciarse para poder guardarlo.
                        </p>

                        <div class="row g-3">

                            <div class="col-12">
                                <label class="form-label fw-semibold">Titulo</label>
                                <input type="text" class="form-control" id="title"  name="title" value="" placeholder="Ingresar titulo">
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
                    <h6 class="mb-0 fw-bold">Sobre los documentos</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">El archivo queda disponible para descarga desde el repositorio de documentos del portal del estudiante.</p>
                </div>
            </div>

        </div>

    </div>

@endsection



@push('scripts')
<script src="{{ asset('managers/js/views/settings/documents/create.js') }}"></script>
@endpush
