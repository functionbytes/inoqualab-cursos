@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            @php
                $coupon_courses = [];
                $coupon_bundles = [];

                if (!empty($coupon->course_ids) && $coupon->course_ids !== 'null') {
                    $coupon_courses = array_values(array_filter(array_map('intval', explode(',', $coupon->course_ids))));
                }

                if (!empty($coupon->bundle_ids) && $coupon->bundle_ids !== 'null') {
                    $coupon_bundles = array_values(array_filter(array_map('intval', explode(',', $coupon->bundle_ids))));
                }

                $start_date = \Carbon\Carbon::parse($coupon->start_date)->format('m/d/Y');
                $end_date   = \Carbon\Carbon::parse($coupon->end_date)->format('m/d/Y');
            @endphp


            <div class="card w-100">

                <form id="formCoupons" enctype="multipart/form-data" role="form"
                      data-urls='@json([
                          "update" => route("manager.coupons.update"),
                          "index" => route("manager.coupons"),
                      ])'
                      data-selected-courses='@json($coupon_courses)'
                      data-selected-bundles='@json($coupon_bundles)'>


                    {{ csrf_field() }}

                    <input type="hidden" id="id" name="id" value="{{ $coupon->id }}">
                    <input type="hidden" id="slack" name="slack" value="{{ $coupon->slack }}">
                    <textarea class="d-none" id="description"
                              name="description">{!! clean($coupon->description, 'content') !!}</textarea>

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Editar cupon</h5>
                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Actualiza los datos del cupón. Los cambios se guardarán al hacer clic en Guardar.
                        </p>
                        <div class="row">

                            <div class="col-12">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Titulo</label>
                                        <input type="text" class="form-control" id="title" name="title" value="{{ $coupon->title }}" placeholder="Ingresar titulo">
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Coupon</label>
                                        <input type="text" class="form-control" id="code" name="code"  value="{{ $coupon->code }}" placeholder="Ingresar codigo">
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Precio minimo</label>
                                        <input type="text" class="form-control" id="min_price" name="min_price" value="{{ $coupon->min_price }}" placeholder="Ingresar precio minimo">
                               </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Monto</label>
                                        <input type="text" class="form-control" id="amount" name="amount" value="{{ $coupon->amount }}" placeholder="Ej: 20">
                                        <small id="amount-hint" class="form-text text-muted"></small>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Uso maximo</label>
                                        <input type="text" class="form-control" id="limit" name="limit" value="{{ $coupon->limit }}"  placeholder="Ej: 100">
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Usos actuales</label>
                                    <input type="text" class="form-control bg-light" value="{{ $usageCount }} / {{ $coupon->limit }}" readonly>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Tipo</label>
                                    <div class="input-group">
                                        {!! Form::select('type', $types, $coupon->type , ['class' => 'select2 form-control','id' => 'type']) !!}
                                    </div>
                                    <label id="type-error" class="error d-none" for="type"></label>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Estado</label>
                                    <div class="input-group">
                                        {!! Form::select('available', $availables, $coupon->available , ['class' => 'select2 form-control','id' => 'available']) !!}
                                    </div>
                                    <label id="available-error" class="error d-none" for="available"></label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Fecha</label>
                                    <div class="input-group">
                                        <input class="form-control date-range-picker date-range" type="text" placeholder="Fecha de inicio - Fecha de término" id="date_var" name="date_var" data-startdate="{{ $start_date }}" data-enddate="{{ $end_date }}">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Cursos</label>
                                    <div class="input-group">
                                        {!! Form::select('courses[]', $courses, $coupon_courses, ['class' => 'select2 form-control', 'multiple' => 'multiple', 'id' => 'courses', 'data-placeholder' => 'Seleccionar cursos (opcional)']) !!}
                                    </div>
                                    <label id="courses-error" class="error d-none" for="courses"></label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Paquetes</label>
                                    <div class="input-group">
                                        {!! Form::select('bundles[]', $bundles, $coupon_bundles, ['class' => 'select2 form-control', 'multiple' => 'multiple', 'id' => 'bundles', 'data-placeholder' => 'Seleccionar paquetes (opcional)']) !!}
                                    </div>
                                    <label id="bundles-error" class="error d-none" for="bundles"></label>
                                </div>
                            </div>


                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="col-form-label">Contenido</label>
                                    <div class="quill-wrapper">
                                        <div id="descriptions">{!! clean($coupon->description, 'content') !!}</div>
                                    </div>
                                    <label id="description-error" class="error d-none" for="description"></label>
                                </div>
                            </div>


                            <div class="col-12">
                                <div class="border-top pt-1 mt-4">
                                    <button type="submit" class="btn btn-info px-4 waves-effect waves-light mt-2 w-100">
                                        Guardar
                                    </button>
                                    <a href="{{ route('manager.coupons') }}" class="btn btn-light px-4 waves-effect mt-2 w-100 text-center">
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
<script src="{{ asset('managers/js/views/settings/coupons/edit.js') }}"></script>
@endpush
