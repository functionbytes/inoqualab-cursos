@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Crear testimonio'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">

            <form id="formTestimonies" enctype="multipart/form-data" role="form"
                  data-urls='@php $__jsonInline1 = [
                      "store" => route("manager.testimonies.store"),
                      "index" => route("manager.testimonies"),
                  ]; @endphp@json($__jsonInline1)'>

                {{ csrf_field() }}

                <input type="hidden" id="id" name="id" value="">
                <input type="hidden" id="slack" name="slack" value="">
                <input type="hidden" id="description" name="description" value="">

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Crear testimonio</h6>
                        <p class="text-muted small mb-0">
                            Completa los datos del testimonio. Los campos marcados como obligatorios deben diligenciarse para poder guardarlo.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-6">
                                <label class="form-label fw-semibold">Nombres</label>
                                <input type="text" class="form-control" id="firstname"  name="firstname" value="" placeholder="Ingresar nombres">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Apellidos</label>
                                <input type="text" class="form-control" id="lastname"  name="lastname" value="" placeholder="Ingresar apellido">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Rol</label>
                                <input type="text" class="form-control" id="role"  name="role" value="" placeholder="Ej: Estudiante certificado">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Ícono (Font Awesome)</label>
                                <input type="text" class="form-control" id="icon"  name="icon" value="" placeholder="Ej: fas fa-user-graduate">
                                <label id="icon-error" class="error d-none" for="icon"></label>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Calificación</label>
                                <div class="input-group">
                                    {!! Form::select('rating', [1 => '1 estrella', 2 => '2 estrellas', 3 => '3 estrellas', 4 => '4 estrellas', 5 => '5 estrellas'], 5, ['class' => 'select2 form-control','id' => 'rating']) !!}
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Número del contador</label>
                                <input type="text" class="form-control" id="counter_value"  name="counter_value" value="" placeholder="Ej: 50">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Sufijo del contador</label>
                                <input type="text" class="form-control" id="counter_suffix"  name="counter_suffix" value="" placeholder="Ej: K+, %, +">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Descripción del contador</label>
                                <input type="text" class="form-control" id="benefit"  name="benefit" value="" placeholder="Ej: Estudiantes certificados con nuestros cursos">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Orden</label>
                                <input type="number" class="form-control" id="position"  name="position" value="0" min="0" placeholder="0">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Estado</label>
                                <div class="input-group">
                                    {!! Form::select('available', $availables, null , ['class' => 'select2 form-control','id' => 'available']) !!}
                                </div>
                                <label id="available-error" class="error d-none" for="available"></label>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Testimonio</label>
                                <div class="quill-wrapper">
                                    <div  id="descriptions"></div>
                                </div>
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
                    <h6 class="mb-0 fw-bold">Sobre los testimonios</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Se muestran en las secciones de testimonios del sitio público. El <strong>número</strong> y <strong>sufijo del contador</strong> son opcionales, para testimonios tipo estadística.</p>
                </div>
            </div>

        </div>

    </div>

@endsection



@push('scripts')
<script src="{{ asset('managers/js/views/settings/testimonies/create.js') }}"></script>
@endpush
