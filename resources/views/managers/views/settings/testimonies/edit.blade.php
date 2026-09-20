@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formTestimonies" enctype="multipart/form-data" role="form"
                      data-urls='@json([
                          "update" => route("manager.testimonies.update"),
                          "index" => route("manager.testimonies"),
                      ])'>

                    {{ csrf_field() }}

                    <textarea class="d-none" id="description" name="description">{!! clean($testimonie->description, 'content') !!}</textarea>
                    <input type="hidden" id="id" name="id" value="{{ $testimonie->id }}">
                    <input type="hidden" id="slack" name="slack" value="{{ $testimonie->slack }}">
                    <input type="hidden" id="edit" name="edit" value="true">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Editar testimonios</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Actualiza los datos del testimonio. Los cambios se guardarán al hacer clic en Guardar.
                        </p>

                        <div class="row">

                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Nombres</label>
                                        <input type="text" class="form-control" id="firstname"  name="firstname" value="{{ $testimonie->firstname }}" placeholder="Ingresar nombres">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Apellidos</label>
                                        <input type="text" class="form-control" id="lastname"  name="lastname" value="{{ $testimonie->lastname }}" placeholder="Ingresar apellido">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Rol</label>
                                        <input type="text" class="form-control" id="role"  name="role" value="{{ $testimonie->role }}" placeholder="Ej: Estudiante certificado">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Ícono (Font Awesome)</label>
                                        <input type="text" class="form-control" id="icon"  name="icon" value="{{ $testimonie->icon }}" placeholder="Ej: fas fa-user-graduate">
                                        <label id="icon-error" class="error d-none" for="icon"></label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Calificación</label>
                                    <div class="input-group">
                                        {!! Form::select('rating', [1 => '1 estrella', 2 => '2 estrellas', 3 => '3 estrellas', 4 => '4 estrellas', 5 => '5 estrellas'], $testimonie->rating, ['class' => 'select2 form-control','id' => 'rating']) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Número del contador</label>
                                        <input type="text" class="form-control" id="counter_value"  name="counter_value" value="{{ $testimonie->counter_value }}" placeholder="Ej: 50">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Sufijo del contador</label>
                                        <input type="text" class="form-control" id="counter_suffix"  name="counter_suffix" value="{{ $testimonie->counter_suffix }}" placeholder="Ej: K+, %, +">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Descripción del contador</label>
                                        <input type="text" class="form-control" id="benefit"  name="benefit" value="{{ $testimonie->benefit }}" placeholder="Ej: Estudiantes certificados con nuestros cursos">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Orden</label>
                                        <input type="number" class="form-control" id="position"  name="position" value="{{ $testimonie->position }}" min="0" placeholder="0">
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Estado</label>
                                    <div class="input-group">
                                        {!! Form::select('available', $availables, $testimonie->available , ['class' => 'select2 form-control','id' => 'available']) !!}
                                    </div>
                                    <label id="available-error" class="error d-none" for="available"></label>
                                </div>
                            </div>

                        <div class="col-12">
                            <div class="mb-3">
                                <label class="col-form-label">Testimonio</label>
                                <div class="quill-wrapper">
                                    <div  id="descriptions">{!! clean($testimonie->description, 'content') !!}</div>
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
<script src="{{ asset('managers/js/views/settings/testimonies/edit.js') }}"></script>
@endpush
