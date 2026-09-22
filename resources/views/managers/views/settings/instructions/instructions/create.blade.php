@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Crear instrucciones'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">

            <form id="formInstructions" enctype="multipart/form-data" role="form"
                  data-urls='@php $__jsonInline1 = [
                      "store" => route("manager.instructions.store"),
                      "index" => route("manager.instructions"),
                  ]; @endphp@json($__jsonInline1)'>

                {{ csrf_field() }}


                <textarea class="d-none" id="short" name="short"></textarea>
                <textarea class="d-none" id="description" name="description"></textarea>

                <div class="card">

                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">Crear instrucciones</h6>
                        <p class="text-muted mb-3">
                            Completa los datos de la instruccion. Los campos marcados como obligatorios deben diligenciarse para poder guardarla.
                        </p>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Titulo</label>
                                <input type="text" class="form-control" id="title"  name="title"  placeholder="Ingresa titulo">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Estado</label>
                                <div class="input-group">
                                    {!! Form::select('available', $availables, null , ['class' => 'select2 form-control' ,'name' => 'available', 'id' => 'available' ]) !!}
                                </div>
                                <label id="available-error" class="error d-none" for="available"></label>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Categorias</label>
                                <div class="input-group">
                                    {!! Form::select('categorie', $categories, null , ['class' => 'select2 form-control' ,'name' => 'categorie', 'id' => 'categorie' ]) !!}
                                </div>
                                <label id="available-error" class="error d-none" for="available"></label>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Etiquetas</label>
                                <input type="text" class="form-control" id="tags" name="tags" value="" >
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Descripcion corta</label>
                                <div id="shorts"></div>
                                <label id="short-error" class="error d-none" for="short"></label>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Descripción</label>
                                <div id="descriptions"></div>
                                <label id="description-error" class="error d-none" for="description"></label>
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
                    <h6 class="mb-0 fw-bold">Sobre las instrucciones</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Guías y manuales de uso organizados por categoría, accesibles desde el sitio público.</p>
                </div>
            </div>

        </div>

    </div>

@endsection



@push('scripts')
<script src="{{ asset('managers/js/views/settings/instructions/instructions/create.js') }}"></script>
@endpush
