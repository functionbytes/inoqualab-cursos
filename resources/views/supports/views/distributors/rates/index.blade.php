@extends('layouts.managers')


@section('page_header')
    @include('supports.includes.card', ['title' => 'Tarifas de cursos'])
@endsection
@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formRates" enctype="multipart/form-data" role="form" onSubmit="return false"
                      data-update-url="{{ route('support.distributors.rates.update') }}"
                      data-redirect-url-template="{{ route('support.distributors.navegation', ':slack') }}">

                    {{ csrf_field() }}

                    <input  id="slack" name="slack" type="hidden" value="{{ $distributor->slack }}">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Tarifas de cursos</h6>
                        <p class="text-muted small mb-0">
                            Define el precio que este distribuidor paga por cada curso. Ajusta los
                            valores y guarda para actualizar todas las tarifas a la vez.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="mb-0 row align-items-center">
                            <div class="table-responsive table-bussiness-hours">
                                <table class="table card-table table-vcenter text-nowrap mb-0">
                                    <thead>
                                    <tr class="">
                                        <th scope="col" class="w-20 border-bottom-0">Cursos</th>
                                        <th scope="col" class="w-20 border-bottom-0">Valor</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    @foreach($rates as $rate)
                                        <tr class="border-bottom-transparent">
                                            <td class="">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <h6 class="fw-semibold mb-1">{{ $rate->course->title }}</h6>
                                                        <p class="fs-2 mb-0 text-muted">{{ $rate->course->categorie->title }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="">
                                                <input type="text" class="form-control" id="courses"  name="courses[{{ $rate->id }}]"   value="{{ $rate->price }}" placeholder="Ingresar precio">
                                            </td>
                                        </tr>
                                    @endforeach

                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>

        </div>

    </div>

@endsection



@push('scripts')
    <script src="{{ asset('supports/js/views/distributors/rates/index.js') }}"></script>
@endpush

