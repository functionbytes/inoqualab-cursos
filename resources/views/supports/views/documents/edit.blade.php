@extends('layouts.managers')


@section('page_header')
    @include('supports.includes.card', ['title' => 'Editar documento'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">

            <form id="formDocuments" enctype="multipart/form-data" role="form"
                  data-update-url="{{ route('support.documents.update') }}"
                  data-index-url="{{ route('support.documents') }}"
                  data-upload-url="{{ route('support.documents.files') }}"
                  data-get-url="{{ route('support.documents.files.get', ':item') }}"
                  data-delete-url="{{ route('support.documents.files.delete', ':id') }}">

                {{ csrf_field() }}

                <input type="hidden" id="description" name="description" value="{{ $document->description  }}">
                <input type="hidden" id="id" name="id" value="{{ $document->id }}">
                <input type="hidden" id="slack" name="slack" value="{{ $document->slack }}">
                <input type="hidden" id="status" name="status" value="{{ $file }}">
                <input type="hidden" id="edit" name="edit" value="true">
                <input type="hidden" id="files" name="files">

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Documento</h6>
                        <p class="text-muted small mb-0">
                            Reemplaza el archivo adjunto si es necesario.</p>
                        </div>

                        <div class="card-body">
                            <div class="dropzone dz-clickable dz-started" id="files">
                            <div class="fallback">
                                <input type="file" hidden name="file">
                            </div>
                        </div>
                        <label id="files-error" class="error d-none" for="files"></label>
                    </div>

                    <hr class="my-0">

                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">Editar documento</h6>
                        <p class="text-muted mb-3">
                            Actualiza los datos del documento. Los cambios se guardarán al hacer clic en Guardar.
                        </p>

                        <div class="row g-3">

                            <div class="col-6">
                                <label class="form-label fw-semibold">Titulo</label>
                                <input type="text" class="form-control" id="title" name="title" value="{{ $document->title }}" placeholder="Ingresar titulo">
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Estado</label>
                                <div class="input-group">
                                    {!! Form::select('available', $availables, $document->available, ['class' => 'select2 form-control','id' => 'available']) !!}
                                </div>
                                <label id="available-error" class="error d-none" for="available"></label>
                            </div>

                            <div class="col-12">
                                <div class="errors d-none"></div>
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
                    <p class="text-muted mb-0">Actualiza el <strong>Estado</strong> para habilitar o deshabilitar la visibilidad del documento.</p>
                </div>
            </div>

        </div>

    </div>

@endsection



@push('scripts')
<script src="{{ asset('supports/js/views/documents/edit.js') }}"></script>
@endpush



