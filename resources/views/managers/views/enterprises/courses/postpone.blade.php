@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formAction" enctype="multipart/form-data" role="form" onSubmit="return false">

                    {{ csrf_field() }}

                    <input name="order" id="order"  type="hidden" value="{{ $order->slack }}">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0"> Modificar fecha de curso</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para que puedas actualizar y modificar la información de manera eficiente y segura. A continuación, encontrarás diversos <mark><code>campos</code></mark> que corresponden a los datos previamente suministrados. Te invitamos a revisar y ajustar cualquier información que consideres necesario actualizar para mantener tus datos al día.
                        </p>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Cliente</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control"  value="{{ Str::upper(Str::lower($user->firstname . ' ' . $user->lastname))  }}" placeholder="Ingresar celular" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Curso</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control"  value="{{ Str::upper(Str::lower($course->title))  }}" placeholder="Ingresar celular" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Fecha</label>
                                    <div class="input-group">
                                        <input type="text" id="range" name="range" class="form-control daterange" />
                                        <span class="input-group-text">
                                          <i class="fas fa-calendar fs-5"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                     <div class="col-12"><div class="action-form border-top mt-4">
                        <div class="text-center">
                            <button type="submit" class="btn btn-info  px-4 waves-effect waves-light mt-2 w-100">
                                Guardar
                            </button>
                        </div>
                        </div>
                    </div>
                </form>
            </div>

        </div>

    </div>

@endsection



@push('scripts')

    <script src="{{ url('managers/libs/daterangepicker/daterangepicker.js') }}" type="text/javascript"></script>
    <script type="text/javascript">
        Dropzone.autoDiscover = false;

        $(document).ready(function() {


            $('.daterange').daterangepicker({
                startDate: {{date('d/m/Y', strtotime($order->enroll_start))}},
                endDate: {{date('d/m/Y', strtotime($order->enroll_expire))}},
                locale: {
                    format: 'MM/DD/YYYY'
                }
            });
            $(".daterange").data().daterangepicker.startDate = moment( {{ $order->enroll_start}}, datepicker.data().daterangepicker.format );
            $(".daterange").data().daterangepicker.endDate = moment( {{ $order->enroll_expire}}, datepicker.data().daterangepicker.format );
            $(".daterange").data().daterangepicker.updateCalendars();
            $("#reportrange").data('daterangepicker').setStartDate(startDate);

            $("#formAction").validate({
                submit: false,
                ignore: ".ignore",
                rules: {
                    range: {
                        required: true,
                    },
                },
                messages: {
                    range: {
                        required: "Es necesario una opción.",
                    },
                },
                submitHandler: function(form) {

                    var $form = $('#formAction');
                    var formData = new FormData($form[0]);
                    var order = $("#order").val();
                    var range = $("#range").val();

                    formData.append('order', order);
                    formData.append('range', range);

                    $.ajax({
                        url: "/manager/enterprises/courses/users/action",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: "POST",
                        contentType: false,
                        processData: false,
                        data: formData,
                        success: function(d) {

                            //window.location.href = "{{ route('manager.enterprises.courses.view',  [$enterprise->slack, $course->slack] ) }}";
                        }
                    });

                }

            });




        });

    </script>



@endpush



