@extends('layouts.pages')


@section('content')

    <main class="main-area fix">

        <!-- breadcrumb-area -->
        <section class="courses__breadcrumb-area">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="courses__breadcrumb-content">
                            <a href="#" class="category">{{ $course->categorie->title }}</a>
                            <h3 class="title">{{ $course->title }}</h3>
                            <ul class="courses__item-meta list-wrap">
                                <li>
                                    <div class="rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <span class="rating-count">(5.0)</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- breadcrumb-area-end -->

        <section class="courses-details-area section-pb-120">
            <div class="container">
                <div class="row">
                    <div class="col-xl-8 col-lg-8">
                        <div class="courses__details-wrapper">
                            <div class="courses__details-curriculum">
                                <div class="course-content">
                                    @if ($course->short != null)
                                        <div class="item-detail mt-0 " >
                                            <div class="section-title">
                                                <h4 class="rbt-title-style-3">De que trata este curso</h4>
                                            </div>
                                            {!! $course->short !!}
                                        </div>
                                    @endif
                                    @if ($course->learn != null)
                                        <div class="item-detail border-top " >
                                            <div class="section-title">
                                                <h4 class="rbt-title-style-3">¿Que aprenderás?</h4>
                                            </div>
                                            {!! $course->learn !!}
                                        </div>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-4">
                        <aside class="courses__details-sidebar">
                            <div class="event-widget">
                                <div class="thumb">
                                    @if(count($course->getMedia('thumbnail'))>0)
                                        <img src="{{ $course->getfirstMedia('thumbnail')->getfullUrl() }}"
                                             onerror="this.src='{{ asset('/pages/images/courses/default.jpg') }}'">
                                    @else
                                        <img src="{{ asset('/pages/images/courses/default.jpg') }}">
                                    @endif
                                    @if ($course->film!=null)
                                        <a href="{{ $course->film }}" class="popup-video"><i class="fas fa-play"></i></a>
                                    @endif
                                </div>
                                <div class="event-cost-wrap">
                                    @if ($course->payment == 1)
                                        @php $regOnSale = $course->promotion == 1 && $course->discount < $course->price; @endphp
                                        @if ($regOnSale)
                                            <h4 class="price"><strong></strong>${{ number_format($course->discount) }} <span>${{ number_format($course->price) }}</span></h4>
                                        @else
                                            <h4 class="price">${{ number_format($course->price) }}</h4>
                                        @endif
                                    @elseif($course->payment == 0)
                                        <div class="rbt-price">
                                            <span class="current-price">GRATIS</span>
                                        </div>
                                        <h4 class="price"><strong>GRATIS</strong></h4>

                                    @endif</div>

                                <div class="event-information-wrap ">
                                    <ul class="list-wrap">
                                        <li><i class="fas fa-stopwatch"></i>Duración <span>{{ $course->duration }}  {{ $course->duration == 1 ? 'Hora' : 'Horas' }}</span></li>
                                        <li><i class="fas fa-bolt"></i>Categoria <span>{{ $course->categorie->title }}</span></li>
                                        <li><i class="fas fa-list"></i>Clases <span>{{ $leasons->count()  }}</span></li>
                                        <li><i class="fas fa-folder"></i>Temas <span>{{ $chapters->count()  }}</span></li>
                                        <li><i class="fas fa-shield-halved"></i>Examen <span>{{ $course->exam == 1 ? 'Si' : 'No' }}</span></li>
                                        <li><i class="fas fa-award"></i>Certificado <span>{{ $course->certificate == 1 ? 'Si' : 'No' }}</span></li>
                                    </ul>


                                    <div class="course-button-wrap">

                                        @if (Auth::check())
                                            @if (count($purchases) >= 0)
                                                @if ($finds->find($course->id) == false)
                                                    <a href="{{ route('checkout', $course->slug) }}" class="btn">ADQUIRIR</a>
                                                @else
                                                    <a href="{{ route('checkout', $course->slug) }}" class="btn">ADQUIRIR</a>
                                                @endif

                                            @endif
                                        @else

                                            <a href="{{ route('checkout', $course->slug) }}" class="btn">ADQUIRIR</a>

                                        @endif

                                        <div class="coursedetails__footer">
                                            <p>Para más detalles</p>
                                            <a href="tel:{!!$about->cellphone!!}"> <i class="fas fa-phone"></i> {{$about->cellphone}}</a>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </aside>
                    </div>
                </div>
            </div>
        </section>
        <div class="coursedetails coursedetails--style2 courses__details-curriculum padding-top padding-bottom brand1-bg-1">
            <div class="container aos-init aos-animate" data-aos="fade-up" data-aos-duration="800">
                <div class="coursedetails__wrapper">
                    <div class="coursedetails__header">
                        <h3>CONTENIDO DEL CURSO</h3>
                        <div class="coursedetails__info">
                            <div class="coursedetails__info-item">
                                <h6>{{ $chapters->count() }}</h6>
                                <span>Temas</span>
                            </div>
                            <div class="coursedetails__info-item">
                                <h6>{{ $leasons->count() }}</h6>
                                <span>Clases</span>
                            </div>
                            <div class="coursedetails__info-item">
                                <h6>{{ $course->exam == 1 ? 'SI' : 'NO' }}</h6>
                                <span>Examen</span>
                            </div>
                        </div>
                    </div>
                    <div class="coursedetails__curriculum">
                        @if ($course->chapters->isNotEmpty())
                            <div class="accordion" id="accordionExample">
                                @php
                                    $count = 0;
                                @endphp

                                @foreach ($course->chapters as $chapter)

                                    @php
                                        $count++
                                    @endphp

                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingOne">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#collapse{{ $chapter->id }}" aria-expanded="true"
                                                    aria-controls="collapse{{ $chapter->id }}">
                                                {{ ucfirst($chapter->title) }}
                                            </button>
                                        </h2>
                                        @if($count == 1)
                                            <div id="collapseOne {{ $chapter->id }}" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                                @else
                                                    <div id="collapseOne {{ $chapter->id }}" class="accordion-collapse collapse " aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                                        @endif
                                                        @foreach ($chapter->lessons as $lesson)
                                                            @if ($lesson->available == 1)
                                                                <div class="accordion-body">
                                                                    <ul class="list-wrap">
                                                                        <li class="course-item">
                                                                            <a href="#" class="course-item-link">
                                                                                <span class="item-name">{{ ucfirst($lesson->title) }}</span>
                                                                                <div class="course-item-meta">
                                                                        <span class="item-meta course-item-status">
                                                                            <img src="/pages/images/icons/lock.svg" alt="icon">
                                                                        </span>
                                                                                </div>
                                                                            </a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                            </div>

                                            @endforeach
                                    </div>
                                    @endif
                            </div>
                    </div>
                </div>
            </div>

            <section class="team team--details padding-top padding-bottom">
                <div class="container aos-init aos-animate" data-aos="fade-up" data-aos-duration="600">
                    <div class="section-header">
                        <h3>CERTIFICADOR</h3>
                    </div>
                    <div class="team__wrapper">
                        <div class="row g-5 align-items-center">
                            <div class="col-md-3 col-sm-12">
                                <div class="team__thumb">
                                    @if($course->certifier->hasMedia('thumbnail'))
                                        <img src="{{ $course->certifier->getFirstMediaUrl('thumbnail') }}" >
                                    @else
                                        <img src="/pages/images/certifier/default.jpg" >
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-9 col-sm-12">
                                <div class="team__content">
                                    <h4 class="mb-0">{{ $course->certifier->firstname . ' ' . $course->certifier->lastname}}</h4>
                                    <p class="designation">{{ $course->certifier->profession }}</p>
                                    @if($course->certifier->description!=null)
                                        <p class="info">{!! $course->certifier->description !!}.</p>
                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        @include ('pages.partials.sections.courses.related')

    </main>


<!-- Start breadcrumb Area -->
<div class="rbt-breadcrumb-default pb--10 pt--100 ptb_md--50 ptb_sm--30 ">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="breadcrumb-inner text-center">
                    <h2 class="title">Facturación</h2>
                    <ul class="page-list">
                        <li class="rbt-breadcrumb-item"><a href="{{ route('index') }}">Inicio</a></li>
                        <li>
                            <div class="icon-right"><i class="fas fa-chevron-right"></i></div>
                        </li>
                        <li class="rbt-breadcrumb-item active">Facturación</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Breadcrumb Area -->

<div class="checkout_area bg-color-white rbt-section-gap">
    <div class="container">
        <div class="row g-5 checkout-form">

            <div class="col-lg-12">
                <div class="row ">
                    <!-- Cart Total -->
                    <div class="col-12 mb--10">

                        <div class="checkout-cart-total">

                            <h4>Curso </h4>

                            <ul>
                                <li>
                                    <span class="title">{{ $course->title }} </span>
                                </li>
                            </ul>


                            <h4 class="mt--30">Total <span>$ {{ number_format($total, 0, ',', '.') }}</span></h4>

                        </div>


                    </div>

                </div>
            </div>
            <div class="col-lg-12">

                <form id="formPayments" enctype="multipart/form-data" role="form" onSubmit="return false">

                        <input name="course" type="hidden" value="{{ $course->slug }}">
                        <input name="token" type="hidden" value="{{ $token }}">
                        <input name="total" type="hidden" value="{{ $total }}">

                        @if (Auth::check())
                            <input name="auth" type="hidden" value="auth">
                        @else
                            <input name="auth" type="hidden" value="unauth">
                        @endif

                        <div class="checkout-content-wrapper">


                        @if (Auth::check())

                            <div id="billing-form">
                                <h4 class="checkout-title">Detalles de facturación</h4>

                                <div class="row">

                                    <div class="col-md-6 col-12 mb--20">
                                        <input type="text" name="firstname" id="firstname"
                                            value="{{ $user->firstname }}" class="form-control" placeholder="Nombres">
                                    </div>

                                    <div class="col-md-6 col-12 mb--20">
                                        <input type="text" id="lastname" name="lastname" value="{{ $user->lastname }}" placeholder="Apellidos"
                                            class="form-control">
                                    </div>

                                    <div class="col-md-6 col-12 mb--20">
                                        <input type="text" id="company" name="company" value="{{ $user->company }}" placeholder="Empresa"
                                            class="form-control">
                                    </div>

                                    <div class="col-md-6 col-12 mb--20">
                                        <input type="text" id="cellphone" name="cellphone" placeholder="Celular"
                                            value="{{ $user->cellphone }}" class="form-control">
                                    </div>

                                    <div class="col-12 mb--20">
                                        <input type="text" id="address" name="address" value="{{ $user->address }}" placeholder="Dirección"
                                            class="form-control">
                                    </div>


                                    <div class="col-md-12 col-12 mb--20">
                                        {!! Form::select('citie', $cities, $citie, ['id' => 'cities', 'class' => 'select2-container']) !!}
                                        <label id="citie-error" class="error d-none" for="citie"></label>
                                    </div>

                                    <div class="col-md-12 col-12 mb--20">
                                        <input type="email" id="email" name="email" value="{{ $user->email }}" class="form-control">
                                    </div>

                                    <div class="col-12 mb--20">
                                        <div class="check-box">
                                            <input name="terms" id="terms" type="checkbox" class="form-check-input">
                                            <label class="form-check-label" for="terms">Acepta los <a
                                                class="text-green" href="{{ route('terms') }}"> términos y
                                                condiciones</a>.</label>
                                        </div>
                                    </div>

                                </div>

                            </div>

                        @else

                            <div id="billing-form">
                                <h4 class="checkout-title">Detalles de facturación</h4>

                                <div class="row">

                                    <div class="col-md-6 col-12 mb--20">
                                        <input type="text" name="firstname" id="firstname" value="" class="form-control" placeholder="Nombres">
                                    </div>

                                    <div class="col-md-6 col-12 mb--20">
                                        <input type="text" id="lastname" name="lastname" value=""class="form-control" placeholder="Apellidos">
                                    </div>

                                    <div class="col-md-6 col-12 mb--20">
                                        <input type="text" id="company" name="company" value="" class="form-control" placeholder="Empresa">
                                    </div>

                                    <div class="col-md-6 col-12 mb--20">
                                        <input type="text" id="cellphone" name="cellphone" value="" class="form-control" placeholder="Celular">
                                    </div>

                                    <div class="col-12 mb--20">
                                        <input type="text" id="address" name="address" value="" class="form-control" placeholder="Dirrección *">
                                    </div>


                                    <div class="col-md-12 col-12 mb--20">
                                        {!! Form::select('citie', $cities, null , ['id' => 'cities', 'class' => '']) !!}
                                    </div>

                                    <div class="col-md-12 col-12 mb--20">
                                        <input type="password" id="password" name="password" class="form-control" autocomplete="new-password" placeholder="Contraseña">
                                    </div>

                                    <div class="col-md-12 col-12 mb--20">
                                        <input type="email" id="email" name="email" class="form-control" autocomplete="off" placeholder="Correo electronico">
                                    </div>

                                    @if ($errors->has('email'))
                                                    <div class="notification error closeable">
                                                    <p>{{ $errors->first('email') }}</p>
                                                    <a class="close"></a>
                                                    </div>
                                            @endif

                                            <div class="col-12 mb--20">
                                                <div class="check-box">
                                                    <input name="terms" id="terms" type="checkbox" class="form-check-input">
                                                    <label class="form-check-label" for="terms">Acepta los <a
                                                        class="text-green" href="{{ route('terms') }}"> términos y
                                                        condiciones</a>.</label>
                                                </div>
                                            </div>

                                </div>

                            </div>

                        @endif


                        <div class="plceholder-button mt--10">
                            <a type="submit" class="rbt-btn btn-gradient hover-icon-reverse payments-disabled" href="javascript:;" id="addPayments">
                                <span class="label">REALIZAR PAGO</span>
                            </a>

                        </div>

                    </form>

                </div>
            </div>


        </div>
    </div>
</div>

<div class="rbt-separator-mid">
    <div class="container">
        <hr class="rbt-separator m-0">
    </div>
</div>


@endsection

@push('scripts')

    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

    <script>

        jQuery.validator.addMethod("emailExt", function(value, element, param) {
            return value.match(/^[a-zA-Z0-9_\.%\+\-]+@[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,3}$/);
        }, 'Porfavor ingrese email valido');

        $("#formPayments").validate({
            submit: false,
            ignore: ":hidden:not(#note),.note-editable.panel-body",
            rules: {
                firstname: {
                    required: true,
                    minlength: 2,
                    maxlength: 200,
                },
                lastname: {
                    required: true,
                    minlength: 2,
                    maxlength: 200,
                },
                address: {
                    required: true,
                    minlength: 10,
                    maxlength: 500,
                },
                company: {
                    required: false,
                    minlength: 4,
                    maxlength: 100,
                },
                cellphone: {
                    required: true,
                    number: true,
                    minlength: 8,
                    maxlength: 500,
                },
                email: {
                    required: true,
                    email: true,
                    emailExt: true,
                },
                citie: {
                    required: true,
                }
            },
            messages: {
                firstname: {
                    required: "El Nombre es necesario",
                    minlength: "El Nombre debe contener al menos 5 caracteres",
                    maxlength: "El Nombre debe contener no mas de 50 caracteres"
                },
                lastname: {
                    required: "El Apellido es necesario.",
                    minlength: "El Apellido debe contener al menos 5 caracteres",
                    maxlength: "El Apellido debe contener no mas de 50 caracteres"
                },
                company: {
                    required: "La empresa es necesario",
                    minlength: "La empresa debe contener al menos 4 caracteres",
                    maxlength: "La empresa debe contener no mas de 100 caracteres",
                    number: "Solo se puede ingresar numeros"
                },
                address: {
                    required: "La dirección línea  es necesaria",
                    minlength: "La dirección  línea 10 debe contener al menos 10 caracteres",
                    maxlength: "Eldirección  línea 500  debe contener no mas de 500 caracteres"
                },
                cellphone: {
                    required: "La celular es necesario",
                    minlength: "La celular debe contener al menos 6 caracteres",
                    maxlength: "La celular debe contener no mas de 20 caracteres",
                    number: "Sólo se pueden ingresar números"
                },
                email: {
                    required: "El email es necesario",
                    email: "Por favor ingrese email valido"
                },
                citie: {
                    required: "El campo ciudad es necesario",
                }
            },

            submitHandler: function(form) {

                var $form = $('#formPayments');
                var formData = new FormData($form[0]);

                $.ajax({
                    url: "/checkout/register",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: "POST",
                    contentType: false,
                    processData: false,
                    data: formData,
                    success: function(data) {


                        
                        if (data == "email") {

                                $('#failed').css("display", "block");

                                setTimeout(function() {
                                    $('#failed').css("display", "none");
                                }, 4000);

                        }

                        if (data == "success") {

                            var token = $("#formPayments").find("input[name='token']").val();
                            var course = $("#formPayments").find("input[name='course']").val();
                            var total = $("#formPayments").find("input[name='total']").val();

                            $.ajax({
                                url: "/checkout/generate",
                                type: "POST",
                                datatype: "json",
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr('content'),
                                    course: course,
                                    token: token,
                                    total: total,
                                },
                                success: function(data) {},
                                error: function(jqXHR, textStatus, errorThrown) {
                                    if (jqXHR.status == 500) {
                                        alert('Internal error: ' + jqXHR.responseText);
                                    } else {
                                        alert('Unexpected error.');
                                    }
                                }
                            });

                            $(".waybox-button").click();

                        }


                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        if (jqXHR.status == 500) {
                            alert('Internal error: ' + jqXHR.responseText);
                        } else {
                            alert('Unexpected error.');
                        }
                    }
                });



            }

        });

        $("#addPayments").click(function() {
            //$('#addPayments').addClass("payments-disabled");
            $("#formPayments").submit();
        });


        $('#cities').select2({
            placeholder: "Seleccionar ciudad",
            ajax: {
                dataType: 'json',
                url: '/cities',
                delay: 250,
                data: function(params) {
                    return {
                        term: $.trim(params.term)
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                },
                cache: true

            }
        });

        $("#terms").on("change", function() {

            value = $(this).is(":checked")

            if (value == true) {
                $('#addPayments').removeClass("payments-disabled")
            } else {
                $('#addPayments').addClass("payments-disabled")
            }


        });
    </script>

@endpush
