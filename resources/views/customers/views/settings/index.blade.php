@extends('layouts.customers')

@section('title', 'Configuración')

@push('css')
    <link rel="stylesheet" href="{{ url('/customers/css/aula.css') }}">
@endpush

@section('content')

@php
    $cityName = ($citie && isset($cities[$citie])) ? $cities[$citie] : ($user->city ?? '');
    $photo = method_exists($user, 'getFirstMediaUrl') ? $user->getFirstMediaUrl('avatar') : '';
@endphp

<section class="cfg-stack">

    {{-- Perfil --}}
    <div class="pnl-card cfg-profile">
        <div class="cfg-av">
            @if($photo)
                <img src="{{ $photo }}" alt="{{ $user->firstname }}" style="width:100%;height:100%;border-radius:inherit;object-fit:cover">
            @else
                <i class="fa-duotone fa-user"></i>
            @endif
        </div>
        <div class="cfg-id">
            <b>{{ ucwords(Str::lower(trim($user->firstname.' '.$user->lastname))) }}</b>
            <span>{{ $user->email }}</span><br/>
            <span class="cfg-role">Estudiante</span>
        </div>
    </div>

    <form id="formUsers" role="form" onsubmit="return false">
        @csrf
        <input type="hidden" id="slack" name="slack" value="{{ $user->slack }}">

        {{-- Información personal --}}
        <div class="pnl-card">
            <div class="pnl-head">
                <h2>Información personal</h2>
                <div class="sub">Mantén tus datos de contacto actualizados.</div>
            </div>
            <div class="pnl-form">
                <div class="field-grid">
                    <div class="field">
                        <label for="cfg_firstname">Nombres</label>
                        <input class="control" id="cfg_firstname" value="{{ $user->firstname }}" readonly>
                    </div>
                    <div class="field">
                        <label for="cfg_lastname">Apellidos</label>
                        <input class="control" id="cfg_lastname" value="{{ $user->lastname }}" readonly>
                    </div>
                    <div class="field">
                        <label for="cfg_identification">Identificación</label>
                        <input class="control" id="cfg_identification" value="{{ $user->identification }}" readonly>
                    </div>
                    <div class="field">
                        <label for="email">Correo electrónico</label>
                        <input class="control" type="email" id="email" name="email" value="{{ $user->email }}" placeholder="Ingresar correo">
                    </div>
                    <div class="field">
                        <label for="cellphone">Celular</label>
                        <input class="control" id="cellphone" name="cellphone" value="{{ $user->cellphone }}" inputmode="tel" placeholder="Ingresar celular">
                    </div>
                    <div class="field">
                        <label for="cfg_city">Ciudad</label>
                        <input class="control" id="cfg_city" value="{{ $cityName }}" readonly>
                    </div>
                    <div class="field full">
                        <label for="address">Dirección</label>
                        <input class="control" id="address" name="address" value="{{ $user->address }}" placeholder="Ingresar dirección">
                    </div>
                </div>
                <button type="submit" class="pnl-save" id="cfgSave">Guardar cambios</button>
            </div>
        </div>

        {{-- Seguridad --}}
        <div class="pnl-card">
            <div class="pnl-head">
                <h2>Seguridad</h2>
                <div class="sub">Deja los campos vacíos si no deseas cambiar tu contraseña.</div>
            </div>
            <div class="pnl-form">
                <div class="field-grid">
                    <div class="field">
                        <label for="password">Nueva contraseña</label>
                        <div class="pw-wrap">
                            <input class="control" type="password" id="password" name="password" placeholder="Mínimo 8 caracteres">
                            <button type="button" class="pw-toggle" id="cfgPwToggle" aria-label="Mostrar contraseña"><i class="fa-solid fa-eye" aria-hidden="true"></i></button>
                        </div>
                    </div>
                    <div class="field">
                        <label for="password_confirmation">Confirmar nueva contraseña</label>
                        <input class="control" type="password" id="password_confirmation" name="password_confirmation" placeholder="Repetir contraseña">
                    </div>
                </div>
                <button type="submit" class="pnl-save" id="cfgPwSave">Actualizar contraseña</button>
            </div>
        </div>
    </form>

    {{-- Notificaciones (preferencias visuales) --}}
    <div class="pnl-card">
        <div class="pnl-head">
            <h2>Notificaciones</h2>
            <div class="sub">Elige qué avisos quieres recibir.</div>
        </div>
        <div class="cfg-notes">
            <label class="switch-row"><div class="stxt"><b>Novedades de cursos</b><span>Avisos cuando se publiquen nuevos cursos o módulos.</span></div><span class="switch"><input type="checkbox" checked><i></i></span></label>
            <label class="switch-row"><div class="stxt"><b>Recordatorios de clases</b><span>Te recordamos continuar tus cursos en progreso.</span></div><span class="switch"><input type="checkbox" checked><i></i></span></label>
            <label class="switch-row"><div class="stxt"><b>Vencimientos y fechas</b><span>Alertas de quizzes, exámenes y cierres próximos.</span></div><span class="switch"><input type="checkbox"><i></i></span></label>
            <label class="switch-row"><div class="stxt"><b>Promociones</b><span>Descuentos y ofertas de capacitación.</span></div><span class="switch"><input type="checkbox"><i></i></span></label>
        </div>
    </div>

</section>
@endsection

@push('scripts')
<script type="text/javascript">
    $(function () {
        // Mostrar/ocultar contraseña
        $('#cfgPwToggle').on('click', function () {
            var $i = $('#password');
            $i.attr('type', $i.attr('type') === 'password' ? 'text' : 'password');
            $(this).find('i').toggleClass('fa-eye fa-eye-slash');
        });

        $('#formUsers').on('submit', function (e) {
            e.preventDefault();

            var password = $('#password').val();
            var confirm = $('#password_confirmation').val();

            if (password && password.length < 8) {
                toastr.warning('La contraseña debe tener al menos 8 caracteres.', 'Advertencia', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
                return;
            }
            if (password && password !== confirm) {
                toastr.warning('Las contraseñas no coinciden.', 'Advertencia', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
                return;
            }

            var formData = new FormData(this);
            var $btns = $('#formUsers button[type="submit"]');
            $btns.prop('disabled', true);

            $.ajax({
                url: "{{ route('customers.settings.update') }}",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                type: 'POST',
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {
                    $btns.prop('disabled', false);
                    if (response.success === true) {
                        toastr.success(response.message, 'Operación exitosa', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
                        $('#password, #password_confirmation').val('');
                    }
                },
                error: function (xhr) {
                    $btns.prop('disabled', false);
                    var msg = 'Se ha generado un error.';
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        msg = Object.values(xhr.responseJSON.errors).map(function (e) { return e[0]; }).join(' ');
                    }
                    toastr.warning(msg, 'Operación fallida', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
                }
            });
        });
    });
</script>
@endpush
