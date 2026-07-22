@extends('layouts.managers')

@section('content')

  <div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">

      <div class="card w-100">



          <div class="card-body border-top">
            <div class="d-flex no-block align-items-center">
              <h5 class="mb-0">Visualizar distribuidor</h5>
            </div>
            <p class="card-subtitle mb-3 mt-3">
              Este espacio está diseñado para que puedas actualizar y modificar la información de manera eficiente y segura. A continuación, encontrarás diversos campos que corresponden a los datos previamente suministrados. Te invitamos a revisar y ajustar cualquier información que consideres necesario actualizar para mantener tus datos al día.
            </p>

            <div class="row">

              <div class="col-6">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Titulo</label>
                  <input type="text" class="form-control" id="title"  name="title"  value="{{ $distributor->title }}"   placeholder="Ingresa titulo" disabled>
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Nit</label>
                  <input type="text" class="form-control" id="nit"  name="nit"  value="{{ $distributor->nit }}"  placeholder="Ingresa nit" disabled>
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Celular</label>
                  <input type="text" class="form-control" id="cellphone"  name="cellphone" value="{{ $distributor->cellphone }}"   placeholder="Ingresa telefono" disabled>
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Dirección</label>
                  <input type="text" class="form-control" id="address"  name="address" value="{{ $distributor->address }}"   placeholder="Ingresa dirección" disabled>
                </div>
              </div>
              <div class="col-12">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Correo electronico</label>
                  <input type="text" class="form-control" id="email"  name="email" value="{{ $distributor->email }}"  placeholder="Ingresa correo electronico" disabled>
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Soporte</label>
                  <input type="text" class="form-control" id="supporting"  name="supporting"  value="{{ $distributor->supporting }}" placeholder="Ingresa el encargado de soporte" disabled>
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Gerente</label>
                  <input type="text" class="form-control" id="leading"  name="leading"  value="{{ $distributor->leading }}" placeholder="Ingresa el encargado de gerente" disabled>
                </div>
              </div>

          </div>

        </form>
      </div>

    </div>

  </div>

@endsection



@push('scripts')

  <script type="text/javascript">

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


      $("#formDistributors").validate({
        submit: false,
        ignore: ".ignore",
        rules: {

          title: {
            required: true,
            minlength: 3,
            maxlength: 100,
          },
          leading: {
            required: true,
            minlength: 3,
            maxlength: 100,
          },
          supporting: {
            required: true,
            minlength: 3,
            maxlength: 100,
          },
          address: {
            required: true,
            minlength: 3,
            maxlength: 100,
          },
          nit: {
            required: true,
            minlength: 6,
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
          available: {
            required: true,
          },

        },
        messages: {
          title: {
            required: "El parametro es necesario.",
            minlength: "Debe contener al menos 3 caracter",
            maxlength: "Debe contener al menos 100 caracter",
          },
          leading: {
            required: "El parametro es necesario.",
            minlength: "Debe contener al menos 3 caracter",
            maxlength: "Debe contener al menos 100 caracter",
          },
          supporting: {
            required: "El parametro es necesario.",
            minlength: "Debe contener al menos 3 caracter",
            maxlength: "Debe contener al menos 100 caracter",
          },
          address: {
            required: "El parametro es necesario.",
            minlength: "Debe contener al menos 0 caracter",
            maxlength: "Debe contener al menos 100 caracter",
          },
          nit: {
            required: "El parametro es necesario.",
            minlength: "Debe contener al menos 6 caracter",
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
          available: {
            required: "Es necesario un estado.",
          },
        },
        submitHandler: function(form) {

          var $form = $('#formDistributors');
          var formData = new FormData($form[0]);
          var title = $("#title").val();
          var cellphone = $("#cellphone").val();
          var email = $("#email").val();
          var address = $("#address").val();
          var nit = $("#nit").val();
          var supporting = $("#supporting").val();
          var leading = $("#leading").val();

          formData.append('title', title);
          formData.append('cellphone', cellphone);
          formData.append('email', email);
          formData.append('address', address);
          formData.append('nit', nit);
          formData.append('supporting', supporting);
          formData.append('leading', leading);

          var $submitButton = $('button[type="submit"]');
          $submitButton.prop('disabled', true);

          $.ajax({
            url: "{{ route('distributor.settings.distributor.update') }}",
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: "POST",
            contentType: false,
            processData: false,
            data: formData,
            success: function(response) {
              if(response.success == true){

                toastr.success(response.message, "Operación exitosa", {
                  closeButton: true,
                  progressBar: true,
                  positionClass: "toast-bottom-right"
                });

                setTimeout(function() {
                  window.location.href = "{{ route('distributor.dashboard') }}";
                }, 2000);

              }else{

                $submitButton.prop('disabled', false);

                toastr.warning("Se ha generado un error.", "Operación fallida", {
                  closeButton: true,
                  progressBar: true,
                  positionClass: "toast-bottom-right"
                });

                error = response.message;
                $('.errors').removeClass('d-none');
                $('.errors').html(error);
              }
            }
          });

        }

      });

    });

  </script>


@endpush
