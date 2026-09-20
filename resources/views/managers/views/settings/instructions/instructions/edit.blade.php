@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formInstructions" enctype="multipart/form-data" role="form"
                      data-urls='@json([
                          "update" => route("manager.instructions.update"),
                          "index" => route("manager.instructions"),
                      ])'>

                    {{ csrf_field() }}

                    <input type="hidden" id="slack" name="slack" value="{{ $instruction->slack }}">
                    <textarea class="d-none" id="short" name="short">{!! clean($instruction->short, 'content') !!}</textarea>
                    <textarea class="d-none" id="description" name="description">{!! clean($instruction->description, 'content') !!}</textarea>
                    
                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Editar instrucciones</h5>
                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Actualiza los datos de la instruccion. Los cambios se guardarán al hacer clic en Guardar.
                        </p>

                        <div class="row">

                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Titulo</label>
                                        <input type="text" class="form-control" id="title"  name="title"  placeholder="Ingresa titulo" value="{{ $instruction->title  }}" >
                                </div>
                            </div>


                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Estado</label>
                                    <div class="input-group">
                                        {!! Form::select('available', $availables, $instruction->available , ['class' => 'select2 form-control' ,'name' => 'available', 'id' => 'available' ]) !!}
                                    </div>
                                    <label id="available-error" class="error d-none" for="available"></label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Categorias</label>
                                    <div class="input-group">
                                        {!! Form::select('categorie', $categories, $instruction->category_id  , ['class' => 'select2 form-control' ,'name' => 'categorie', 'id' => 'categorie' ]) !!}
                                    </div>
                                    <label id="available-error" class="error d-none" for="available"></label>
                                </div>
                            </div>


                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Etiquetas</label>
                                    <input type="text" class="form-control" id="tags" name="tags" value="{{ $instruction->tags  }}" >
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <label class="control-label col-form-label">Descripcion corta</label>
                                <div>
                                    <div id="shorts">{!! clean($instruction->short, 'content') !!}</div>
                                </div>
                                <label id="short-error" class="error d-none" for="short"></label>
                            </div>
                            <div class="col-12 mt-3">
                                <label class="control-label col-form-label">Descripción</label>
                                <div>
                                    <div id="descriptions">{!! clean($instruction->description, 'content') !!}</div>
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
<script src="{{ asset('managers/js/views/settings/instructions/instructions/edit.js') }}"></script>
@endpush
