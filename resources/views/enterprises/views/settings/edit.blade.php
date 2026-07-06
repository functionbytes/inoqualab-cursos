@extends('layouts.managers')

@section('content')

  <div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">

      <div class="card w-100">

        <form id="formUsers" enctype="multipart/form-data" role="form" onSubmit="return false">

          {{ csrf_field() }}

          <input type="hidden" id="id" name="id" value="{{ $user->id }}">
          <input type="hidden" id="slack" name="slack" value="{{ $user->slack }}">

          <div class="card-body border-top">
            <div class="d-flex no-block align-items-center">
              <h5 class="mb-0">Mi perfil</h5>
            </div>
            <p class="card-subtitle mb-3 mt-3">
              Este espacio está diseñado para que puedas actualizar y modificar la información de manera eficiente y segura. A continuación, encontrarás diversos <mark><code>campos</code></mark> que corresponden a los datos previamente suministrados. Te invitamos a revisar y ajustar cualquier información que consideres necesario actualizar para mantener tus datos al día.
            </p>

            <div class="row">

              <div class="col-6">
                <div class="mb-3">
                  <label class="control-label col-form-label">Nombres</label>
                  <input type="text" class="form-control" id="firstname" name="firstname" value="{{ $user->firstname }}" placeholder="Ingresar nombres">
                </div>
              </div>
              <div class="col-6">
                <div class="mb-3">
                  <label class="control-label col-form-label">Apellidos</label>
                  <input type="text" class="form-control" id="lastname" name="lastname" value="{{ $user->lastname }}" placeholder="Ingresar apellido">
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <label class="control-label col-form-label">Identificación</label>
                  <input type="text" class="form-control" id="identification" name="identification" value="{{ $user->identification }}" placeholder="Ingresar identificación">
                </div>
              </div>
              <div class="col-6">
                <div class="mb-3">
                  <label class="control-label col-form-label">Correo electrónico</label>
                  <input type="text" class="form-control" id="email" value="{{ $user->email }}" readonly>
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <label class="control-label col-form-label">Dirección</label>
                  <input type="text" class="form-control" id="address" name="address" value="{{ $user->address }}" placeholder="Ingresar dirección">
                </div>
              </div>
              <div class="col-6">
                <div class="mb-3">
                  <label class="control-label col-form-label">Contraseña</label>
                  <input type="password" class="form-control" id="password" name="password" value="" placeholder="Ingresar contraseña">
                </div>
              </div>

              <div class="col-12">
                <div class="errors d-none"></div>
              </div>

              <div class="col-12">
                <div class="border-top pt-1 mt-4">
                  <button type="submit" class="btn btn-info px-4 waves-effect waves-light mt-2 w-100">
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
          address: {
            required: false,
            minlength: 3,
            maxlength: 100,
          },
          password: {
            required: false,
            minlength: 6,
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
            minlength: "Debe contener al menos 3 caracter",
            maxlength: "Debe contener al menos 100 caracter",
          },
          address: {
            minlength: "Debe contener al menos 3 caracter",
            maxlength: "Debe contener al menos 100 caracter",
          },
          password: {
            minlength: "Debe contener al menos 6 caracteres",
            maxlength: "No puede superar los 100 caracteres",
          },
        },
        submitHandler: function(form) {

          var $form = $('#formUsers');
          var formData = new FormData($form[0]);
          var $submitButton = $('button[type="submit"]');
          $submitButton.prop('disabled', true);

          $.ajax({
            url: "{{ route('enterprise.profile.update') }}",
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: "POST",
            contentType: false,
            processData: false,
            data: formData,
            success: function(response) {

              $submitButton.prop('disabled', false);

              if (response.success) {
                toastr.success("Perfil actualizado correctamente");
                $('#password').val('');
                $('.errors').addClass('d-none').html('');
              } else {
                toastr.warning(response.message || "Se ha generado un error.");
                $('.errors').removeClass('d-none').html(response.message || '');
              }

            },
            error: function(xhr) {

              $submitButton.prop('disabled', false);

              var messages = [];

              if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                $.each(xhr.responseJSON.errors, function(field, errs) {
                  messages.push(errs[0]);
                });
              } else {
                messages.push('Se ha generado un error inesperado.');
              }

              toastr.warning("Revisa los datos del formulario.");
              $('.errors').removeClass('d-none').html(messages.join('<br>'));
            }
          });

        }

      });

    });

  </script>

@endpush
