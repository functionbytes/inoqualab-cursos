@extends('layouts.managers')


@section('page_header')
    @include('supports.includes.card', ['title' => 'Editar instrucciones'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">

            <form id="formInstructions" enctype="multipart/form-data" role="form"
                  data-update-url="{{ route('support.instructions.update') }}"
                  data-redirect-url="{{ route('support.instructions') }}">

                {{ csrf_field() }}

                <input type="hidden" id="slack" name="slack" value="{{ $instruction->slack }}">
                <input type="hidden" id="description" name="description" value="{{ $instruction->description }}">

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Editar instrucciones</h6>
                        <p class="text-muted small mb-0">
                            Actualiza los datos de la instrucción. Los cambios se guardarán al hacer clic en Guardar.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-6">
                                <label class="form-label fw-semibold">Titulo</label>
                                <input type="text" class="form-control" id="title" name="title" placeholder="Ingresa titulo" value=" {{ $instruction->title  }}">
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Estado</label>
                                <div class="input-group">
                                    {!! Form::select('available', $availables, $instruction->available , ['class' => 'select2 form-control' ,'name' => 'available', 'id' => 'available' ]) !!}
                                </div>
                                <label id="available-error" class="error d-none" for="available"></label>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Categorias</label>
                                <div class="input-group">
                                    {!! Form::select('categorie', $categories, $instruction->category_id  , ['class' => 'select2 form-control' ,'name' => 'categorie', 'id' => 'categorie' ]) !!}
                                </div>
                                <label id="available-error" class="error d-none" for="available"></label>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Descripción</label>
                                <div id="descriptions">{!! clean($instruction->description, 'content') !!}</div>
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
                    <p class="text-muted mb-0">Selecciona la <strong>Categoría</strong> a la que pertenece para que aparezca correctamente organizada en el centro de ayuda.</p>
                </div>
            </div>

        </div>

    </div>

@endsection



@push('scripts')
<script src="{{ asset('supports/js/views/instructions/instructions/edit.js') }}"></script>
@endpush



