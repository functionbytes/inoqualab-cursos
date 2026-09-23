@extends('layouts.customers')

@section('title', 'Mi cuenta')

@push('css')
    <link rel="stylesheet" href="{{ url('/customers/css/aula.css') }}?v={{ @filemtime(public_path('customers/css/aula.css')) ?: 1 }}">
@endpush

@section('content')

@php
    $cityName = ($citie && isset($cities[$citie])) ? $cities[$citie] : ($user->city ?? '');

    // Porcentaje de perfil completo: los seis campos que la ficha del alumno
    // usa para certificados y facturación.
    $campos = [
        'identificación' => $user->identification,
        'celular' => $user->cellphone,
        'dirección' => $user->address,
        'nombres' => $user->firstname,
        'apellidos' => $user->lastname,
        'correo' => $user->email,
    ];
    $rellenos = collect($campos)->filter(fn ($v) => filled($v))->count();
    $completo = (int) round($rellenos / count($campos) * 100);
    $faltan = collect($campos)->filter(fn ($v) => blank($v))->keys();

    $nombreCompleto = ucwords(Str::lower(trim($user->firstname.' '.$user->lastname)));
@endphp

{{-- Misma banda de contexto que el resto del portal (Cursos, Certificados,
     Documentos...), con la fila de tabs de esta página insertada debajo. --}}
@section('context-title', 'Mi cuenta')
@section('context-icon')@include('customers.includes.icon', ['name' => 'gear'])@endsection
@section('context-subtitle')
    {{ $nombreCompleto }} · {{ $user->email }}
    @if($user->created_at)
        · Estudiante desde {{ Str::replace('.', '', $user->created_at->translatedFormat('M Y')) }}
    @endif
    · {{ $user->inscriptions_count }} {{ Str::plural('curso', $user->inscriptions_count) }}
    · {{ $user->certificates_count }} {{ Str::plural('certificado', $user->certificates_count) }}
@endsection
@section('context-stat-number', $completo.'%')
@section('context-stat-label', 'Perfil completo')
@section('context-tabs')
    <button type="button" class="is-active" data-tab="perfil" role="tab" aria-selected="true">Perfil</button>
    <button type="button" data-tab="seguridad" role="tab" aria-selected="false">Seguridad</button>
    <button type="button" data-tab="avisos" role="tab" aria-selected="false">Notificaciones</button>
    <button type="button" data-tab="privacidad" role="tab" aria-selected="false">Privacidad</button>
@endsection

<section class="sb-page">

    <div class="sb-body">

        <form id="formLogout" method="POST" action="{{ route('logout') }}">@csrf</form>

        <form id="formUsers" role="form" data-update-url="{{ route('customers.settings.update') }}">
            @csrf
            <input type="hidden" id="slack" name="slack" value="{{ $user->slack }}">

            {{-- Perfil --}}
            <div class="sb-split" data-panel="perfil">
                <div class="pnl-card">
                    <div class="pnl-head">
                        <h2>Datos personales</h2>
                        <div class="sub">Aparecen en tus facturas y en el certificado que se emite al aprobar cada curso.</div>
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
                                <label for="cfg_identification_type">Tipo de identificación</label>
                                <input class="control" id="cfg_identification_type" value="{{ $user->identification_type }}" placeholder="Sin registrar" readonly>
                            </div>
                            <div class="field">
                                <label for="cfg_identification">Número de identificación</label>
                                <input class="control {{ $user->identification ? '' : 'is-missing' }}"
                                       id="cfg_identification"
                                       value="{{ $user->identification }}"
                                       placeholder="Requerido para certificados" readonly>
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
                            <div class="field">
                                <label for="address">Dirección</label>
                                <input class="control" id="address" name="address" value="{{ $user->address }}" placeholder="Ingresar dirección">
                            </div>
                        </div>
                    </div>
                    <div class="pnl-foot">
                        <button type="submit" class="pnl-save is-blue" id="cfgSave">Guardar cambios</button>
                    </div>
                </div>

                <aside class="sb-aside">
                    @if($faltan->isNotEmpty())
                        <div class="sb-todo">
                            <h4>Completa tu perfil</h4>
                            <p>Sin número de identificación no podemos imprimir tu nombre legal en los certificados ni emitir facturas.</p>
                            <div class="items">
                                @foreach($faltan as $campo)
                                    <div class="item is-pending">
                                        @include('customers.includes.icon', ['name' => 'circle-alert', 'size' => 15])
                                        {{ ucfirst($campo) }} pendiente
                                    </div>
                                @endforeach
                                <div class="item is-done">
                                    @include('customers.includes.icon', ['name' => 'check', 'size' => 15])
                                    Correo registrado
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="sb-session">
                        <div class="sb-session-head">
                            <h4>Sesión activa</h4>
                        </div>
                        <dl class="sb-session-meta">
                            <div>
                                <dt>Dispositivo</dt>
                                <dd>Este equipo</dd>
                            </div>
                            @if($user->last_login_at)
                                <div>
                                    <dt>Desde</dt>
                                    <dd>{{ $user->last_login_at->translatedFormat('d M Y, H:i') }}</dd>
                                </div>
                            @endif
                        </dl>
                        <p>Solo puedes tener una sesión abierta a la vez. Si entras desde otro equipo, esta se cerrará automáticamente.</p>
                        {{-- El form vive fuera de #formUsers (HTML no admite
                             formularios anidados); el boton lo referencia. --}}
                        <button type="submit" form="formLogout" class="sb-logout">
                            Cerrar sesión
                        </button>
                    </div>
                </aside>
            </div>

            {{-- Seguridad --}}
            <div class="sb-narrow" data-panel="seguridad" hidden>
                <div class="pnl-card">
                    <div class="pnl-head">
                        <h2>Contraseña</h2>
                        <div class="sub">Usa al menos 8 caracteres. Deja los campos vacíos si no deseas cambiarla.</div>
                    </div>
                    <div class="pnl-form">
                        <div class="field-grid">
                            <div class="field">
                                {{-- Se exige también si el correo cambió en la pestaña Perfil
                                     de este mismo formulario -- cambiar cualquiera de los dos
                                     sin volver a confirmar la contraseña permitiría tomar la
                                     cuenta con una sesión robada (XSS, equipo compartido). --}}
                                <label for="current_password">Contraseña actual</label>
                                <div class="pw-wrap">
                                    <input class="control" type="password" id="current_password" name="current_password" placeholder="Requerida para cambiar el correo o la contraseña" autocomplete="current-password">
                                    <button type="button" class="pw-toggle" data-target="current_password" aria-label="Mostrar contraseña">
                                        <span class="ic-on">@include('customers.includes.icon', ['name' => 'eye', 'size' => 16])</span>
                                        <span class="ic-off">@include('customers.includes.icon', ['name' => 'eye-off', 'size' => 16])</span>
                                    </button>
                                </div>
                            </div>
                            <div class="field">
                                <label for="password">Nueva contraseña</label>
                                <div class="pw-wrap">
                                    <input class="control" type="password" id="password" name="password" placeholder="Mínimo 8 caracteres">
                                    <button type="button" class="pw-toggle" data-target="password" aria-label="Mostrar contraseña">
                                        <span class="ic-on">@include('customers.includes.icon', ['name' => 'eye', 'size' => 16])</span>
                                        <span class="ic-off">@include('customers.includes.icon', ['name' => 'eye-off', 'size' => 16])</span>
                                    </button>
                                </div>
                            </div>
                            <div class="field">
                                <label for="password_confirmation">Confirmar nueva contraseña</label>
                                <div class="pw-wrap">
                                    <input class="control" type="password" id="password_confirmation" name="password_confirmation" placeholder="Repetir contraseña">
                                    <button type="button" class="pw-toggle" data-target="password_confirmation" aria-label="Mostrar contraseña">
                                        <span class="ic-on">@include('customers.includes.icon', ['name' => 'eye', 'size' => 16])</span>
                                        <span class="ic-off">@include('customers.includes.icon', ['name' => 'eye-off', 'size' => 16])</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="pnl-foot">
                        <button type="submit" class="pnl-save is-blue" id="cfgPwSave">Actualizar contraseña</button>
                    </div>
                </div>

                <div class="pnl-card sb-activity">
                    <div class="pnl-head">
                        <h2>Actividad de la cuenta</h2>
                    </div>
                    <div class="entry">
                        <span class="ic is-live">@include('customers.includes.icon', ['name' => 'circle-check', 'size' => 17])</span>
                        <div class="txt">
                            <b>Sesión actual</b>
                            <span>Este dispositivo{{ $user->last_login_ip ? ' · '.$user->last_login_ip : '' }}</span>
                        </div>
                        <span class="tag">Activa</span>
                    </div>
                    @if($user->last_login_at)
                        <div class="entry">
                            <span class="ic">@include('customers.includes.icon', ['name' => 'clock', 'size' => 17])</span>
                            <div class="txt">
                                <b>Último acceso registrado</b>
                                <span>{{ $user->last_login_at->translatedFormat('d M Y, H:i') }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </form>

        {{-- Notificaciones (preferencias visuales) --}}
        <div class="sb-narrow" data-panel="avisos" hidden>
            <div class="pnl-card">
                <div class="pnl-head">
                    <h2>Avisos por correo</h2>
                    <div class="sub">Elige qué quieres recibir en {{ $user->email }}.</div>
                    <div class="cfg-warn">Estas preferencias todavía no se guardan: la pantalla las muestra, pero el envío de avisos no las tiene en cuenta.</div>
                </div>
                <div class="cfg-notes">
                    <label class="switch-row"><div class="stxt"><b>Novedades de cursos</b><span>Cuando se publiquen nuevos cursos o módulos.</span></div><span class="switch"><input type="checkbox" checked><i></i></span></label>
                    <label class="switch-row"><div class="stxt"><b>Recordatorios de clase</b><span>Si llevas más de una semana sin entrar.</span></div><span class="switch"><input type="checkbox" checked><i></i></span></label>
                    <label class="switch-row"><div class="stxt"><b>Vencimientos de acceso</b><span>Aviso 15 días antes de que caduque un curso.</span></div><span class="switch"><input type="checkbox"><i></i></span></label>
                    <label class="switch-row"><div class="stxt"><b>Pedidos y facturas</b><span>Confirmación de compra y comprobantes.</span></div><span class="switch"><input type="checkbox" checked><i></i></span></label>
                </div>
            </div>
        </div>

        {{-- Privacidad y datos: informativo, fuera de #formUsers porque no
             envía nada. Reutiliza .cfg-privacy, que ya existe en portal.css. --}}
        <div class="sb-narrow" data-panel="privacidad" hidden>
            <div class="pnl-card">
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
<script src="{{ asset('customers/js/views/settings/index-b.js') }}"></script>
@endpush
