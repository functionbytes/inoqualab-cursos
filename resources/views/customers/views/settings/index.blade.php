@extends('layouts.customers')

@section('title', 'Configuración')

@push('css')
    <link rel="stylesheet" href="{{ url('/customers/css/aula.css') }}?v={{ @filemtime(public_path('customers/css/aula.css')) ?: 1 }}">
@endpush

@section('content')

@php
    $cityName = ($citie && isset($cities[$citie])) ? $cities[$citie] : ($user->city ?? '');
    $photo = method_exists($user, 'getFirstMediaUrl') ? $user->getFirstMediaUrl('avatar') : '';
    $iniciales = Str::upper(Str::substr($user->firstname, 0, 1).Str::substr($user->lastname, 0, 1));

    $faltan = collect([
        ! $user->identification ? 'identificación' : null,
        ! $user->cellphone ? 'celular' : null,
    ])->filter()->values();
@endphp

<section class="cfg-page">

    <div class="cfg-title">
        <h1>Configuración de la cuenta</h1>
        <p>Tus datos aparecen en las facturas y en los certificados que emitimos.</p>
    </div>

    <div class="cfg-layout">

        {{-- Índice de secciones: la página es larga y antes había que recorrerla
             entera para llegar a la contraseña o a los avisos. --}}
        <nav class="cfg-side" aria-label="Secciones de la configuración">
            <a href="#cfg-personal" class="is-on">@include('customers.includes.icon', ['name' => 'user-grad']) Información personal</a>
            <a href="#cfg-seguridad">@include('customers.includes.icon', ['name' => 'lock']) Seguridad</a>
            <a href="#cfg-avisos">@include('customers.includes.icon', ['name' => 'bell']) Notificaciones</a>
            <a href="#cfg-privacidad">@include('customers.includes.icon', ['name' => 'shield-check']) Privacidad y datos</a>
        </nav>

        <div class="cfg-col">

            <form id="formUsers" role="form" data-update-url="{{ route('customers.settings.update') }}">
                @csrf
                <input type="hidden" id="slack" name="slack" value="{{ $user->slack }}">

                {{-- Perfil + información personal: una sola tarjeta, la identidad
                     encabeza los datos que se editan justo debajo. --}}
                <div class="pnl-card" id="cfg-personal">
                    <div class="cfg-ident">
                        <div class="cfg-av">
                            @if($photo)
                                <img src="{{ $photo }}" alt="{{ $user->firstname }}">
                            @else
                                {{ $iniciales }}
                            @endif
                        </div>
                        <div class="cfg-id">
                            <b>{{ ucwords(Str::lower(trim($user->firstname.' '.$user->lastname))) }}</b>
                            <span>{{ $user->email }}</span>
                            <span class="cfg-role">Estudiante</span>
                        </div>
                    </div>

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
                                <label for="cfg_identification">
                                    Identificación
                                    @unless($user->identification)
                                        <span class="lb-warn">· requerida para certificados</span>
                                    @endunless
                                </label>
                                <input class="control {{ $user->identification ? '' : 'is-missing' }}"
                                       id="cfg_identification"
                                       value="{{ $user->identification }}"
                                       placeholder="Pendiente de registrar" readonly>
                            </div>
                            <div class="field">
                                <label for="email">Correo electrónico</label>
                                <input class="control" type="email" id="email" name="email" value="{{ $user->email }}" placeholder="Ingresar correo">
                            </div>
                            <div class="field">
                                <label for="cellphone">Celular</label>
                                <input class="control {{ $user->cellphone ? '' : 'is-missing' }}" id="cellphone" name="cellphone" value="{{ $user->cellphone }}" inputmode="tel" placeholder="Ingresar celular">
                            </div>
                            <div class="field">
                                <label for="cfg_city">Ciudad</label>
                                <input class="control" id="cfg_city" value="{{ $cityName }}" placeholder="Sin ciudad registrada" readonly>
                            </div>
                            <div class="field full">
                                <label for="address">Dirección</label>
                                <input class="control" id="address" name="address" value="{{ $user->address }}" placeholder="Ingresar dirección">
                            </div>
                        </div>

                        <div class="pnl-actions">
                            <button type="submit" class="pnl-save" id="cfgSave">Guardar cambios</button>
                            <span class="hint">Los cambios se aplican de inmediato</span>
                        </div>
                    </div>
                </div>

                {{-- Seguridad --}}
                <div class="pnl-card" id="cfg-seguridad">
                    <div class="pnl-head">
                        <h2>Seguridad</h2>
                        <div class="sub">Deja los campos vacíos si no deseas cambiar tu contraseña.</div>
                    </div>

                    @if($user->last_login_at)
                        <div class="cfg-lastlogin">
                            @include('customers.includes.icon', ['name' => 'clock', 'size' => 17])
                            <div>Último acceso: <b>{{ $user->last_login_at->translatedFormat('d M Y, H:i') }}</b> · solo puedes tener una sesión abierta a la vez</div>
                        </div>
                    @endif

                    <div class="pnl-form">
                        <div class="field-grid">
                            <div class="field">
                                {{-- Se exige también si el correo cambió en la sección Perfil
                                     de este mismo formulario -- ver UpdateSettingsRequest. --}}
                                <label for="current_password">Contraseña actual</label>
                                <input class="control" type="password" id="current_password" name="current_password" placeholder="Requerida para cambiar el correo o la contraseña" autocomplete="current-password">
                            </div>
                            <div class="field">
                                <label for="password">Nueva contraseña</label>
                                <div class="pw-wrap">
                                    <input class="control" type="password" id="password" name="password" placeholder="Mínimo 8 caracteres">
                                    <button type="button" class="pw-toggle" id="cfgPwToggle" aria-label="Mostrar contraseña">@include('customers.includes.icon', ['name' => 'search', 'size' => 16])</button>
                                </div>
                            </div>
                            <div class="field">
                                <label for="password_confirmation">Confirmar nueva contraseña</label>
                                <input class="control" type="password" id="password_confirmation" name="password_confirmation" placeholder="Repetir contraseña">
                            </div>
                        </div>
                        <button type="submit" class="pnl-save is-ghost" id="cfgPwSave">Actualizar contraseña</button>
                    </div>
                </div>
            </form>

            {{-- Notificaciones (preferencias visuales) --}}
            <div class="pnl-card" id="cfg-avisos">
                <div class="pnl-head">
                    <h2>Notificaciones</h2>
                    <div class="sub">Elige qué avisos quieres recibir por correo.</div>
                    <div class="cfg-warn">Estas preferencias todavía no se guardan: la pantalla las muestra, pero el envío de avisos no las tiene en cuenta.</div>
                </div>
                <div class="cfg-notes">
                    <label class="switch-row"><div class="stxt"><b>Novedades de cursos</b><span>Avisos cuando se publiquen nuevos cursos o módulos.</span></div><span class="switch"><input type="checkbox" checked><i></i></span></label>
                    <label class="switch-row"><div class="stxt"><b>Recordatorios de clases</b><span>Te recordamos continuar tus cursos en progreso.</span></div><span class="switch"><input type="checkbox" checked><i></i></span></label>
                    <label class="switch-row"><div class="stxt"><b>Vencimientos de acceso</b><span>Aviso 15 días antes de que caduque el acceso a un curso.</span></div><span class="switch"><input type="checkbox"><i></i></span></label>
                </div>
            </div>

            {{-- Privacidad y datos --}}
            <div class="pnl-card" id="cfg-privacidad">
                <div class="pnl-head">
                    <h2>Privacidad y datos</h2>
                    <div class="sub">Qué guardamos de tu cuenta y para qué se usa.</div>
                </div>
                <div class="cfg-privacy">
                    <div class="entry">
                        <b>Datos de identidad</b>
                        <span>Nombre, apellidos e identificación. Se imprimen en los certificados y en las facturas.</span>
                    </div>
                    <div class="entry">
                        <b>Datos de contacto</b>
                        <span>Correo, celular y dirección. Se usan para avisarte de tus cursos y para la facturación.</span>
                    </div>
                    <div class="entry">
                        <b>Actividad de formación</b>
                        <span>Lecciones vistas, resultados de quizzes y exámenes. Sostienen tu progreso y la emisión de certificados.</span>
                    </div>
                    @if($user->created_at)
                        <div class="entry">
                            <b>Antigüedad de la cuenta</b>
                            <span>Estudiante desde {{ $user->created_at->translatedFormat('F \d\e Y') }}.</span>
                        </div>
                    @endif
                </div>
                <a class="cfg-privacy-link" href="{{ route('terms') }}" target="_blank" rel="noopener">Ver términos y tratamiento de datos</a>
            </div>

        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('customers/js/views/settings/index.js') }}"></script>
@endpush
