@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formRates" enctype="multipart/form-data" role="form" onSubmit="return false"
                      data-update-url="{{ route('support.distributors.rates.update') }}"
                      data-redirect-url-template="{{ route('support.distributors.navegation', ':slack') }}">

                    {{ csrf_field() }}

                    <input  id="slack" name="slack" type="hidden" value="{{ $distributor->slack }}">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0"> Tarifas de cursos</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para que puedas actualizar y modificar la información de manera eficiente y segura. A continuación, encontrarás diversos campos que corresponden a los datos previamente suministrados. Te invitamos a revisar y ajustar cualquier información que consideres necesario actualizar para mantener tus datos al día.
                        </p>

                        <div class="mb-4 row align-items-center">
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
    <script src="{{ asset('supports/js/views/distributors/rates/index.js') }}"></script>
@endpush

