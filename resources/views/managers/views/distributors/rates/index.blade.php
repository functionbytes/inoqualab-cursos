@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Tarifas de cursos'])
@endsection
@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formRates" enctype="multipart/form-data" role="form"
                      data-update-url="{{ route('manager.distributors.rates.update') }}"
                      data-navegation-url="{{ route('manager.distributors.navegation', ':slack') }}">

                    {{ csrf_field() }}

                    <input  id="slack" name="slack" type="hidden" value="{{ $distributor->slack }}">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0"> Tarifas de cursos</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Define el valor de cada curso para este distribuidor. Los precios se actualizarán al hacer clic en Guardar.
                        </p>

                        <div class="mb-4 row align-items-center">
                            <div class="table-responsive table-bussiness-hours">
                                <table class="table card-table table-vcenter text-nowrap mb-0">
                                    <thead>
                                    <tr class="">
                                        <th class="w-20 border-bottom-0">Cursos</th>
                                        <th class="w-20 border-bottom-0">Valor</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    @foreach($rates as $rate)
                                        <tr class="border-bottom-transparent">
                                            <td class="">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <h6 class="fw-semibold mb-1">{{ $rate->course->title }}</h6>
                                                        @if($rate->course?->categorie)
                                                            <span class="badge bg-primary-subtle text-primary">{{ $rate->course->categorie->title }}</span>
                                                        @else
                                                            <span class="badge bg-secondary-subtle text-secondary">Sin categoría</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="">
                                                <input type="text" class="form-control" id="courses_{{ $rate->id }}"  name="courses[{{ $rate->id }}]"   value="{{ $rate->price }}" placeholder="Ingresar precio">
                                            </td>
                                        </tr>
                                    @endforeach

                                    </tbody>
                                </table>
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
<script src="{{ asset('managers/js/views/distributors/rates/index.js') }}"></script>
@endpush

