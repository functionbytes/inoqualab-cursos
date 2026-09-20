@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formCertification" enctype="multipart/form-data" role="form"
                      data-urls='@php $__jsonInline1 = [
                          "store" => route("manager.certifications.store"),
                          "index" => route("manager.certifications"),
                          "thumbnails" => route("manager.certifications.thumbnails"),
                          "thumbnailDelete" => route("manager.certifications.thumbnails.delete", ":id"),
                      ]; @endphp@json($__jsonInline1)'>

                    {{ csrf_field() }}

                    <input type="hidden" id="description" name="description" value="">
                    <input type="hidden" id="id" name="id" value="">
                    <input type="hidden" id="slack" name="slack" value="">
                    <input type="hidden" id="status" name="status" value="false">
                    <input type="hidden" id="edit" name="edit" value="true">
                    <input type="hidden" id="thumbnail" name="thumbnail">
                    
                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Cerificado</h5>
                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Sube la imagen del certificado. El formato recomendado es JPG o PNG.
                        </p>
                        <div class="dropzone dz-clickable" id="thumbnail">
                            <div class="fallback">
                                <input type="file" hidden name="file">
                            </div>
                        </div>
                        <label id="thumbnail-error" class="error d-none" for="thumbnail"></label>
                    </div>

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Crear certificación</h5>
                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Completa los datos de la certificación. Los campos marcados como obligatorios deben diligenciarse para poder guardarla.
                        </p>
                        <div class="row">

                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Titulo</label>
                                        <input type="text" class="form-control" id="title"  name="title" value="" placeholder="Ingresar titulo">
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Estado</label>
                                    <div class="input-group">
                                        {!! Form::select('available', $availables, null , ['class' => 'select2 form-control','id' => 'available']) !!}
                                    </div>
                                    <label id="available-error" class="error d-none" for="available"></label>
                                </div>
                            </div>


                         <div class="col-12">
                             <div class="mb-3">
                                 <label class="col-form-label">Descripción</label>
                                 <div class="quill-wrapper">
                                     <div id="descriptions"></div>
                                 </div>
                                 <label id="description-error" class="error d-none" for="description"></label>
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
<script src="{{ asset('managers/js/views/settings/certifications/create.js') }}"></script>
@endpush
