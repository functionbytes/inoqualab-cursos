@extends('layouts.managers')

@section('content')

  <div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">

      <div class="card w-100">

        <form id="formUsers" enctype="multipart/form-data" role="form" onSubmit="return false">

          {{ csrf_field() }}

          <input type="hidden" id="id" name="id" value="">
          <input type="hidden" id="slack" name="slack" value="">
          <input type="hidden" id="edit" name="edit" value="true">
          <input type="hidden" id="enterprise" name="enterprise" value="{{ $enterprise->slack }}">

          <div class="card-body border-top">
            <div class="d-flex no-block align-items-center">

              <h5 class="mb-0">Crear usuario
              </h5>

            </div>
            <p class="card-subtitle mb-3 mt-3">
              Completa los datos del nuevo usuario de la empresa. Los campos marcados como obligatorios deben diligenciarse para poder guardarlo.
            </p>

            <div class="row">

              <div class="col-6">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Nombres</label>
                  <input type="text" class="form-control" id="firstname"  name="firstname" value="" placeholder="Ingresar nombres">
                </div>
              </div>
              <div class="col-6">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Apellidos</label>
                  <input type="text" class="form-control" id="lastname"  name="lastname" value="" placeholder="Ingresar apellido">
                </div>
              </div>

              <div class="col-12">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Correo electronico</label>
                  <input type="text" class="form-control" id="email"  name="email" value="" placeholder="Ingresar correo electronico" autocomplete="new-password" >
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Dirección</label>
                  <input type="text" class="form-control" id="address"  name="address" value="" placeholder="Ingresar dirección">
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Identificación</label>
                  <input type="text" class="form-control" id="identification"  name="identification" value="" placeholder="Ingresar identificación">
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Celular</label>
                  <input type="text" class="form-control" id="cellphone"  name="cellphone" value="" placeholder="Ingresar celular">
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <div class="mb-3">
                    <label  class="control-label col-form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password"  name="password" value="" placeholder="Ingresar contraseña" autocomplete="new-password">
                  </div>
                </div>
              </div>

              <div class="col-12">
                <div class="errors d-none">
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

  <div id="course-modal" class="modal fade">
    <div class="modal-dialog modal-md modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
        </div>
        <div class="modal-body text-center">
          <div class="display-4 text-danger"><i data-feather="x-octagon"></i></div>
          <h4 class="my-0">¿Deseas asignar este usuario a un curso?</h4>
          <p>Visualizaras los cursos de la empresa</p>
          <div class="row justify-content-center mt-20  ">
            <div class="col-sm-12 col-md-5">
              <a href="" id="course-link" class="btn btn-danger w-100">Confirmar</a>
            </div>
            <div class="col-sm-12 col-md-5">
              <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">Cancelar</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

@endsection



@push('scripts')

  <script type="text/javascript">
    Dropzone.autoDiscover = false;

    $(document).ready(function() {


      jQuery.validator.addMethod(
              'emailExt',
              function (value, element, param) {
                return value.match(
                        /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i,
                )
              },
              'Porfavor ingrese email valido',
      );

      $("#formUsers").validate({
        submit: false,
        ignore: ".ignore",
        rules: {
          firstname: {
            required: true,
            minlength: 3,
            maxlength: 100,
          },
          lastname: {
            required: true,
            minlength: 3,
            maxlength: 100,
          },
          identification: {
            required: false,
            minlength: 3,
            maxlength: 100,
          },
          cellphone: {
            required: false,
            number: true,
            minlength: 6,
            maxlength: 10,
          },
          email: {
            required: true,
            email: true,
            emailExt: true,
          },
          address: {
            required: false,
            minlength: 3,
            maxlength: 100,
          },
          password: {
            required: false,
            minlength: 3,
            maxlength: 100,
          },

        },
        messages: {
          firstname: {
            required: "El parametro es necesario.",
            minlength: "Debe contener al menos 3 caracter",
            maxlength: "Debe contener al menos 100 caracter",
          },
          lastname: {
            required: "El parametro es necesario.",
            minlength: "Debe contener al menos 3 caracter",
            maxlength: "Debe contener al menos 100 caracter",
          },
          identification: {
            required: "El parametro es necesario.",
            minlength: "Debe contener al menos 3 caracter",
            maxlength: "Debe contener al menos 100 caracter",
          },
          cellphone: {
            required: "El parametro es necesario.",
            number: 'Solo se puede ingresar números.',
            minlength: "Debe contener al menos 6 caracter",
            maxlength: "Debe contener al menos 10 caracter",
          },
          email: {
            required: 'Tu email ingresar correo electrónico es necesario.',
            email: 'Por favor, introduce una dirección de correo electrónico válida.',
          },
          address: {
            required: "El parametro es necesario.",
            minlength: "Debe contener al menos 3 caracter",
            maxlength: "Debe contener al menos 100 caracter",
          },
          password: {
            required: "El parametro es necesario.",
            minlength: "Debe contener al menos 6 caracter",
            maxlength: "Debe contener al menos 10 caracter",
          },
        },
        submitHandler: function(form) {

          var $form = $('#formUsers');
          var formData = new FormData($form[0]);
          var slack = $("#slack").val();
          var firstname = $("#firstname").val();
          var lastname = $("#lastname").val();
          var identification = $("#identification").val();
          var cellphone = $("#cellphone").val();
          var email = $("#email").val();
          var address = $("#address").val();
          var password = $("#password").val();
          var enterprise = $("#enterprises").val();

          formData.append('slack', slack);
          formData.append('firstname', firstname);
          formData.append('lastname', lastname);
          formData.append('identification', identification);
          formData.append('cellphone', cellphone);
          formData.append('email', email);
          formData.append('address', address);
          formData.append('password', password);
          formData.append('enterprises', enterprise);

          var $submitButton = $('button[type="submit"]');
          $submitButton.prop('disabled', true);

          $.ajax({
            url: "{{ route('manager.enterprises.users.store') }}",
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: "POST",
            contentType: false,
            processData: false,
            data: formData,
            success: function(response) {

              if(response.success == true){

                var url = "{{ route('manager.enterprises.users', $enterprise->slack) }}";

                $("#firstname").val('');
                $("#lastname").val('');
                $("#identification").val('');
                $("#cellphone").val('');
                $("#email").val('');
                $("#address").val('');
                $("#password").val('');

                $("#course-modal").modal("show");
                $("#course-link").attr("href", url);

                $submitButton.prop('disabled', false);

              }else{

                $submitButton.prop('disabled', false);

                let error = response.message;
                $('.errors').removeClass('d-none');
                $('.errors').html(error);

                setTimeout(function() {
                  $('.errors').addClass('d-none');
                  $('.errors').html();
                }, 2000);

              }
            }

          });

        }

      });



    });

  </script>

@endpush



