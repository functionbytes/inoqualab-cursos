@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Editar cupon'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

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

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">

            <form id="formCoupons" enctype="multipart/form-data" role="form"
                  data-urls='@php $__jsonInline1 = [
                      "update" => route("manager.coupons.update"),
                      "index" => route("manager.coupons"),
                  ]; @endphp@json($__jsonInline1)'
                  data-selected-courses='@json($coupon_courses)'
                  data-selected-bundles='@json($coupon_bundles)'>


                {{ csrf_field() }}

                <input type="hidden" id="id" name="id" value="{{ $coupon->id }}">
                <input type="hidden" id="slack" name="slack" value="{{ $coupon->slack }}">
                <textarea class="d-none" id="description"
                          name="description">{!! clean($coupon->description, 'content') !!}</textarea>

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Editar cupon</h6>
                        <p class="text-muted small mb-0">
                            Actualiza los datos del cupón. Los cambios se guardarán al hacer clic en Guardar.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-12">
                                <label class="form-label fw-semibold">Titulo</label>
                                <input type="text" class="form-control" id="title" name="title" value="{{ $coupon->title }}" placeholder="Ingresar titulo">
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Coupon</label>
                                <input type="text" class="form-control" id="code" name="code"  value="{{ $coupon->code }}" placeholder="Ingresar codigo">
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Precio minimo</label>
                                <input type="text" class="form-control" id="min_price" name="min_price" value="{{ $coupon->min_price }}" placeholder="Ingresar precio minimo">
                                <small class="text-muted d-block mt-1">Opcional. Monto mínimo de compra para que el cupón aplique.</small>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Monto</label>
                                <input type="text" class="form-control" id="amount" name="amount" value="{{ $coupon->amount }}" placeholder="Ej: 20">
                                <small id="amount-hint" class="form-text text-muted"></small>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Uso maximo</label>
                                <input type="text" class="form-control" id="limit" name="limit" value="{{ $coupon->limit }}"  placeholder="Ej: 100">
                                <small class="text-muted d-block mt-1">Opcional. Vacío = sin límite de usos.</small>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Usos actuales</label>
                                <input type="text" class="form-control bg-light" value="{{ $usageCount }} / {{ $coupon->limit }}" readonly>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Tipo</label>
                                <div class="input-group">
                                    {!! Form::select('type', $types, $coupon->type , ['class' => 'select2 form-control','id' => 'type']) !!}
                                </div>
                                <label id="type-error" class="error d-none" for="type"></label>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Estado</label>
                                <div class="input-group">
                                    {!! Form::select('available', $availables, $coupon->available , ['class' => 'select2 form-control','id' => 'available']) !!}
                                </div>
                                <label id="available-error" class="error d-none" for="available"></label>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Fecha</label>
                                <div class="input-group">
                                    <input class="form-control date-range-picker date-range" type="text" placeholder="Fecha de inicio - Fecha de término" id="date_var" name="date_var" data-startdate="{{ $start_date }}" data-enddate="{{ $end_date }}">
                                </div>
                                <small class="text-muted d-block mt-1">Opcional. Vacío = el cupón no vence.</small>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Cursos</label>
                                <div class="input-group">
                                    {!! Form::select('courses[]', $courses, $coupon_courses, ['class' => 'select2 form-control', 'multiple' => 'multiple', 'id' => 'courses', 'data-placeholder' => 'Seleccionar cursos (opcional)']) !!}
                                </div>
                                <label id="courses-error" class="error d-none" for="courses"></label>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Paquetes</label>
                                <div class="input-group">
                                    {!! Form::select('bundles[]', $bundles, $coupon_bundles, ['class' => 'select2 form-control', 'multiple' => 'multiple', 'id' => 'bundles', 'data-placeholder' => 'Seleccionar paquetes (opcional)']) !!}
                                </div>
                                <label id="bundles-error" class="error d-none" for="bundles"></label>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Contenido</label>
                                <div class="quill-wrapper">
                                    <div id="descriptions">{!! clean($coupon->description, 'content') !!}</div>
                                </div>
                                <label id="description-error" class="error d-none" for="description"></label>
                            </div>

                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            Guardar
                        </button>
                        <a href="{{ route('manager.coupons') }}" class="btn btn-light w-100 text-center">
                            Cancelar
                        </a>
                    </div>

                </div>
            </form>
        </div>

        {{-- Columna derecha: sidebar informativo --}}
        <div class="col-lg-4">

            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Sobre los cupones</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Si no seleccionas cursos ni paquetes, el cupón queda disponible para todo el catálogo.</p>
                    <p class="text-muted mb-0">El campo <strong>Monto</strong> se interpreta como porcentaje o valor fijo según el <strong>Tipo</strong> elegido.</p>
                </div>
            </div>

        </div>

    </div>

@endsection



@push('scripts')
<script src="{{ asset('managers/js/views/settings/coupons/edit.js') }}"></script>
@endpush
