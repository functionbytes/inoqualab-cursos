@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formBundles" enctype="multipart/form-data" role="form"
                      data-urls='@php $__jsonInline1 = [
                          "store" => route("manager.bundles.store"),
                          "index" => route("manager.bundles"),
                          "thumbnails" => route("manager.bundles.thumbnails"),
                          "thumbnailDelete" => route("manager.bundles.thumbnails.delete", ":id"),
                      ]; @endphp@json($__jsonInline1)'>

                    {{ csrf_field() }}

                    <input type="hidden" id="id" name="id" value="">
                    <input type="hidden" id="slack" name="slack" value="">
                    <textarea class="d-none" id="description" name="description"></textarea>
                    <textarea class="d-none" id="meta_description" name="meta_description"></textarea>
                    <input type="hidden" id="status" name="status" value="false">
                    <input type="hidden" id="edit" name="edit" value="true">
                    <input type="hidden" id="thumbnail" name="thumbnail">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Imagen</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para que puedas actualizar y modificar la foto de tu perfil es necesario actualizar para mantener tus datos al día.
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
                            <h5 class="mb-0">Crear paquete</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Completa los datos del paquete. Los campos marcados como obligatorios deben diligenciarse para poder guardarlo.
                        </p>
                        <div class="row">

                            <div class="col-12">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Titulo</label>
                                        <input type="text" class="form-control" id="title"  name="title" value=""  placeholder="Ingresar titulo">
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Precio</label>
                                        <input type="text" class="form-control" id="price"  name="price" value=""  placeholder="Ingresar precio">
                                        <small class="form-text text-muted">Monto en COP</small>
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

                            <div class="col-6">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Fecha inicio</label>
                                    <div class="input-group">
                                        <input type="date" id="start_date" name="start_date" class="form-control daterange" />
                                    </div>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Fecha final</label>
                                    <div class="input-group">
                                        <input type="date" id="expire_at" name="expire_at" class="form-control daterange" />
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Cursos</label>
                                    <div class="input-group">
                                        {!! Form::select('courses[]', $courses, null, ['class' => 'select2 form-control', 'multiple' => 'multiple', 'id' => 'courses', 'data-placeholder' => 'Seleccionar cursos...']) !!}
                                    </div>
                                    <label id="bundles-error" class="error d-none" for="bundles"></label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Descripción</label>
                                        <div class="quill-wrapper">
                                            <div  id="descriptions"></div>
                                        </div>
                                        <label id="description-error" class="error d-none" for="description"></label>
                                </div>
                            </div>

                            <div class="col-12">
                                <hr>
                                <h6 class="fw-bold mb-1 border-bottom pb-2">Información SEO</h6>
                                <p class="text-muted small mb-3">Datos opcionales para posicionamiento en buscadores.</p>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Título SEO</label>
                                    <input type="text" class="form-control" id="meta_title" name="meta_title" value="" placeholder="Ingresar título SEO">
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Palabras clave</label>
                                    <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" value="">
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="border-top pt-1 mt-4">
                                    <button type="submit" class="btn btn-info px-4 waves-effect waves-light mt-2 w-100">
                                        Guardar
                                    </button>
                                    <a href="{{ route('manager.bundles') }}" class="btn btn-light px-4 waves-effect mt-2 w-100 text-center">
                                        Cancelar
                                    </a>
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
<script src="{{ asset('managers/js/views/settings/bundles/create.js') }}"></script>
@endpush
