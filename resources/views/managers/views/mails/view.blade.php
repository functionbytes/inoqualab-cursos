@extends('layouts.managers')

@section('page_header')
    @include('managers.includes.card', ['title' => 'Detalle del correo'])
@endsection

@section('content')


    @if($mail->status === 'processed')
        <div class="alert alert-success d-flex align-items-center gap-2 mb-3" role="alert">
            <i class="fas fa-lock fs-4"></i>
            <div>
                <strong>Correo procesado</strong><br>
                <small>
                    Este correo ya generó una orden correctamente. Solo puedes ver la información, no realizar cambios.
                    @if($mail->order)
                        — <a href="{{ route('manager.orders.view', $mail->order->slack) }}" class="alert-link">Ver orden #{{ $mail->order->number ?? $mail->order->id }}</a>
                    @endif
                </small>
            </div>
        </div>
    @endif

    @if($mail->status === 'ignored')
        <div class="alert alert-secondary d-flex align-items-center gap-2 mb-3" role="alert">
            <i class="fas fa-ban fs-4"></i>
            <div><strong>Correo descartado</strong> — Este correo fue marcado como ignorado y no generará una orden.</div>
        </div>
    @endif

    @if($mail->status === 'failed')
        <div class="alert alert-danger mb-3">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="fas fa-circle-exclamation fs-4 flex-shrink-0"></i>
                <strong>Error al procesar</strong>
            </div>
            @if($mail->error_log)
                <p class="mb-2 small">{{ $mail->error_log }}</p>
            @endif
            @php
                $checkFields = [
                    'document'        => ['Documento/Cédula', $payload['document'] ?? null],
                    'name'            => ['Nombre',          $payload['name'] ?? null],
                    'enterprise_code' => ['Código empresa',  $payload['enterprise_code'] ?? null],
                    'enterprise_name' => ['Nombre empresa',  $payload['enterprise_name'] ?? null],
                    'courses'         => ['Cursos',          !empty($payload['courses']) ? $payload['courses'] : null],
                ];
            @endphp
            <div class="mt-1">
                <div class="small fw-semibold mb-2 text-danger-emphasis">Campos extraídos del correo:</div>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($checkFields as [$label, $value])
                        @php $ok = !empty($value); @endphp
                        <span class="badge {{ $ok ? 'bg-success' : 'bg-light text-danger border border-danger' }} rounded-3 py-1 px-2">
                            <i class="fas {{ $ok ? 'fa-check' : 'fa-xmark' }} me-1"></i>{{ $label }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if($duplicates->isNotEmpty())
        <div class="alert alert-warning d-flex gap-2 mb-3" role="alert">
            <i class="fas fa-copy fs-5 flex-shrink-0 mt-1"></i>
            <div>
                <strong>Posible correo duplicado</strong> — En las últimas 48 horas se recibieron otros correos del mismo remitente:<br>
                <ul class="mb-0 mt-1 small ps-3">
                    @foreach($duplicates as $dup)
                        @php
                            $dupStatus = [
                                'pending_review' => 'Pendiente',
                                'processed'      => 'Procesado',
                                'failed'         => 'Fallido',
                                'ignored'        => 'Ignorado',
                            ][$dup->status] ?? $dup->status;
                        @endphp
                        <li>
                            <a href="{{ route('manager.mails.show', $dup->slack) }}" class="alert-link fw-semibold">
                                {{ \Str::limit($dup->subject, 60) }}
                            </a>
                            — {{ \Carbon\Carbon::parse($dup->received_at)->diffForHumans() }}
                            <span class="text-muted">({{ $dupStatus }})</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @php $isReadOnly = in_array($mail->status, ['processed', 'ignored']); @endphp
    <div class="row g-3" id="mails-view"
         data-config='@php $__jsonInline1 = [
            "readOnly" => $isReadOnly,
            "routes" => [
                "reviewers" => route("manager.mails.reviewers"),
                "assign" => route("manager.mails.assign", $mail->slack),
                "courses" => route("manager.mails.courses"),
                "note" => route("manager.mails.note", $mail->slack),
                "confirm" => route("manager.mails.confirm", $mail->slack),
                "orderView" => route("manager.orders.view", ":slack"),
                "index" => route("manager.mails.index"),
                "discard" => route("manager.mails.discard", $mail->slack),
                "reparse" => route("manager.mails.reparse", $mail->slack),
            ],
         ]; @endphp@json($__jsonInline1)'>

        {{-- ==================== SIDEBAR IZQUIERDA col-lg-4 ==================== --}}
        <div class="col-12 col-lg-4">

            {{-- Datos del correo --}}
            <div class="card mb-3">
                <div class="card-header p-3 border-bottom">
                    <h6 class="mb-0 fw-bold">Datos del correo</h6>
                    <p class="text-muted">Información del mensaje recibido</p>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <h6 class="text-muted fw-semibold small mb-1">REMITENTE</h6>
                            <p class="mb-0 fw-semibold">{{ $mail->from }}</p>
                        </div>
                        <div class="col-12">
                            <h6 class="text-muted fw-semibold small mb-1">ASUNTO</h6>
                            <p class="mb-0">{{ $mail->subject }}</p>
                        </div>
                        <div class="col-12">
                            <h6 class="text-muted fw-semibold small mb-1">RECIBIDO</h6>
                            <p class="mb-0 small">
                                {{ \Carbon\Carbon::parse($mail->received_at)->format('d/m/Y H:i:s') }}<br>
                                <span class="text-muted">{{ \Carbon\Carbon::parse($mail->received_at)->diffForHumans() }}</span>
                            </p>
                        </div>
                        <div class="col-12">
                            <h6 class="text-muted fw-semibold small mb-1">ESTADO</h6>
                            @php
                                $statusMap = [
                                    'pending_review' => ['bg-light-warning text-warning', 'Pendiente revisión'],
                                    'processed'      => ['bg-light-success text-success', 'Procesado'],
                                    'failed'         => ['bg-light-danger text-danger',   'Fallido'],
                                    'ignored'        => ['bg-light-secondary text-secondary', 'Ignorado'],
                                ];
                                [$sc, $sl] = $statusMap[$mail->status] ?? ['bg-light-secondary text-secondary', $mail->status];
                            @endphp
                            <span class="badge {{ $sc }} rounded-3 py-1 px-2 fw-semibold">{{ $sl }}</span>
                        </div>
                        @if(!is_null($mail->confidence_score))
                            <div class="col-12">
                                <h6 class="text-muted fw-semibold small mb-1">CONFIANZA</h6>
                                @php $score = $mail->confidence_score; $bc = $score >= 90 ? 'bg-success' : ($score >= 50 ? 'bg-warning' : 'bg-danger'); @endphp
                                <span class="badge {{ $bc }} text-white rounded-3 py-1 px-2">{{ $score }}%</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Datos extraídos del correo --}}
            @if(!empty($payload))
                <div class="card mb-3">
                    <div class="card-header p-3 border-bottom">
                        <h6 class="mb-0 fw-bold">Datos extraídos</h6>
                        <p class="text-muted">Información parseada del cuerpo del correo</p>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @if(!empty($payload['document']))
                                <div class="col-12">
                                    <h6 class="text-muted fw-semibold small mb-1">DOCUMENTO</h6>
                                    <p class="mb-0 fw-semibold">{{ $payload['document'] }}</p>
                                </div>
                            @endif
                            @if(!empty($payload['name']))
                                <div class="col-12">
                                    <h6 class="text-muted fw-semibold small mb-1">NOMBRE</h6>
                                    <p class="mb-0">{{ $payload['name'] }}</p>
                                </div>
                            @endif
                            @if(!empty($payload['enterprise_raw']) || !empty($payload['enterprise_code']))
                                <div class="col-12">
                                    <h6 class="text-muted fw-semibold small mb-1">EMPRESA DETECTADA</h6>
                                    <p class="mb-0 small">
                                        {{ $payload['enterprise_raw'] ?? '' }}
                                        @if(!empty($payload['enterprise_code']))
                                            <code class="text-primary ms-1">({{ $payload['enterprise_code'] }})</code>
                                        @endif
                                    </p>
                                </div>
                            @endif
                            @if(!empty($payload['enterprise_name']))
                                <div class="col-12">
                                    <h6 class="text-muted fw-semibold small mb-1">NOMBRE EMPRESA</h6>
                                    <p class="mb-0">{{ $payload['enterprise_name'] }}</p>
                                </div>
                            @endif
                            @if(!empty($payload['ips']))
                                <div class="col-12">
                                    <h6 class="text-muted fw-semibold small mb-1">IPS</h6>
                                    <p class="mb-0">{{ is_array($payload['ips']) ? implode(', ', $payload['ips']) : $payload['ips'] }}</p>
                                </div>
                            @endif
                            @if(!empty($payload['courses']))
                                <div class="col-12">
                                    <h6 class="text-muted fw-semibold small mb-1">CURSOS EN EL CORREO</h6>
                                    <ul class="list-unstyled mb-0 mt-1">
                                        @foreach($payload['courses'] as $courseText)
                                            <li class="d-flex align-items-start gap-2 mb-1">
                                                <i class="fas fa-book-open text-primary mt-1 mails-view-text-xs"></i>
                                                <span class="small">{{ $courseText }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Asignar a revisor --}}
            <div class="card mb-3">
                <div class="card-header p-3 border-bottom">
                    <h6 class="mb-0 fw-bold">Asignar a revisor</h6>
                    <p class="text-muted">
                        @if($mail->assignedUser)
                            Actualmente: <strong>{{ trim($mail->assignedUser->firstname.' '.$mail->assignedUser->lastname) }}</strong>
                        @else
                            Sin asignar
                        @endif
                    </p>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2">
                        <select id="assign-user-select" class="form-select form-select-sm flex-grow-1">
                            <option value="">Sin asignar</option>
                            @if($mail->assignedUser)
                                <option value="{{ $mail->assignedUser->id }}" selected>
                                    {{ trim($mail->assignedUser->firstname.' '.$mail->assignedUser->lastname) }}
                                </option>
                            @endif
                        </select>
                        <button type="button" id="assign-btn" class="btn btn-sm btn-outline-primary flex-shrink-0">
                            <i class="fas fa-user-check"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Acciones rápidas --}}
            <div class="card mb-3">
                <div class="card-header p-3 border-bottom">
                    <h6 class="mb-0 fw-bold">Acciones rápidas</h6>
                    <p class="text-muted">Opciones disponibles</p>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($mail->status === 'processed' && $mail->order)
                            <a href="{{ route('manager.orders.view', $mail->order->slack) }}" class="btn btn-success">
                                Ver orden generada
                            </a>
                        @endif
                        @if(!in_array($mail->status, ['processed', 'ignored']))
                            <button type="button" id="reparse-btn" class="btn btn-outline-info"
                                    data-slack="{{ $mail->slack }}">
                                Re-analizar correo
                            </button>
                            <button type="button" id="discard-btn" class="btn btn-outline-secondary">
                                Descartar
                            </button>
                        @endif
                        <a href="{{ route('manager.mails.index') }}" class="btn btn-light">
                            Volver al listado
                        </a>
                    </div>
                </div>
            </div>

            {{-- Notas internas --}}
            <div class="card mb-3">
                <div class="card-header p-3 border-bottom">
                    <h6 class="mb-0 fw-bold">Notas internas</h6>
                    <p class="text-muted">Comentarios del equipo (no visibles al cliente)</p>
                </div>
                <div class="card-body">
                    <textarea id="notes-textarea" class="form-control form-control-sm mails-notes-textarea"
                              rows="3" placeholder="Añadir nota...">{{ $mail->notes ?? '' }}</textarea>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <p class="text-muted"><span id="notes-chars">{{ strlen($mail->notes ?? '') }}</span> caracteres</p>
                        <button type="button" id="save-notes-btn" class="btn btn-sm btn-outline-primary">
                            Guardar nota
                        </button>
                    </div>
                </div>
            </div>

            {{-- Historial de cambios --}}
            @if($timeline->count())
                <div class="card mb-3">
                    <div class="card-header p-3 border-bottom">
                        <h6 class="mb-0 fw-bold">Historial</h6>
                        <p class="text-muted">Seguimiento de cambios de estado</p>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-unstyled mb-0">
                            @foreach($timeline as $activity)
                                @php
                                    $props   = $activity->properties->toArray();
                                    $attrs   = $props['attributes'] ?? [];
                                    $old     = $props['old'] ?? [];
                                    $newSt   = $attrs['status'] ?? null;

                                    if ($activity->event === 'created') {
                                        $icon = 'fa-envelope'; $color = 'text-primary'; $label = 'Correo recibido';
                                    } elseif ($newSt === 'processed') {
                                        $icon = 'fa-circle-check'; $color = 'text-success'; $label = 'Procesado correctamente';
                                    } elseif ($newSt === 'failed') {
                                        $icon = 'fa-circle-exclamation'; $color = 'text-danger'; $label = 'Procesamiento fallido';
                                    } elseif ($newSt === 'ignored') {
                                        $icon = 'fa-ban'; $color = 'text-secondary'; $label = 'Descartado';
                                    } elseif ($newSt === 'pending_review') {
                                        $icon = 'fa-clock'; $color = 'text-warning'; $label = 'En revisión';
                                    } elseif (array_keys($attrs) === ['notes']) {
                                        $icon = 'fa-note-sticky'; $color = 'text-info'; $label = 'Nota guardada';
                                    } else {
                                        $icon = 'fa-pen'; $color = 'text-info'; $label = 'Datos actualizados';
                                    }
                                @endphp
                                <li class="d-flex align-items-start gap-2 p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                    <div class="mt-1 flex-shrink-0">
                                        <i class="fas {{ $icon }} {{ $color }} mails-timeline-icon"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold small {{ $color }}">{{ $label }}</div>
                                        <div class="text-muted mails-view-text-xs">
                                            {{ \Carbon\Carbon::parse($activity->created_at)->format('d/m/Y H:i') }}
                                            @if($activity->causer)
                                                — {{ trim($activity->causer->firstname . ' ' . $activity->causer->lastname) }}
                                            @endif
                                        </div>
                                        @if(!empty($attrs['order_id']))
                                            <div class="small text-muted mt-1">Orden #{{ $attrs['order_id'] }}</div>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

        </div>

        {{-- ==================== COLUMNA DERECHA col-lg-8 ==================== --}}
        <div class="col-12 col-lg-8">

            {{-- Confirmar orden --}}
            <div class="card mb-3">
                <form id="confirmForm">
                    <div class="card-header p-3 border-bottom {{ $isReadOnly ? 'bg-light' : '' }}">
                        <h6 class="mb-0 fw-bold">Confirmar orden</h6>
                        <p class="text-muted">
                            @if($isReadOnly)
                                Vista de solo lectura — correo {{ $mail->status === 'processed' ? 'procesado' : 'descartado' }}
                            @else
                                Verificar empresa y mapeo de cursos antes de crear la orden
                            @endif
                        </p>
                    </div>
                    <div class="card-body">

                        {{-- Correcciones al payload (solo editable) --}}
                        @if(!$isReadOnly)
                        <div class="mb-3">
                            <button type="button" class="btn btn-link btn-sm p-0 text-muted" id="toggle-overrides">
                                Editar campos extraídos
                            </button>
                            <div id="overrides-panel" class="mt-2 p-3 border rounded bg-light d-none">
                                <p class="text-muted mb-2 mails-view-text-xs">Sobreescribe los datos del correo antes de crear la orden. Deja vacío para usar el valor original.</p>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label form-label-sm mb-1">Documento</label>
                                        <input type="text" class="form-control form-control-sm payload-override"
                                               data-key="document" value="{{ $payload['document'] ?? '' }}" placeholder="Sin datos">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label form-label-sm mb-1">Nombre</label>
                                        <input type="text" class="form-control form-control-sm payload-override"
                                               data-key="name" value="{{ $payload['name'] ?? '' }}" placeholder="Sin datos">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label form-label-sm mb-1">Código empresa</label>
                                        <input type="text" class="form-control form-control-sm payload-override"
                                               data-key="enterprise_code" value="{{ $payload['enterprise_code'] ?? '' }}" placeholder="Sin datos">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label form-label-sm mb-1">Nombre empresa</label>
                                        <input type="text" class="form-control form-control-sm payload-override"
                                               data-key="enterprise_name" value="{{ $payload['enterprise_name'] ?? '' }}" placeholder="Sin datos">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Empresa --}}
                        <div class="mb-3">
                            <h6 class="text-muted fw-semibold small mb-1">EMPRESA</h6>
                            <select class="form-select select2" id="enterprise_id" name="enterprise_id" {{ $isReadOnly ? 'disabled' : '' }}>
                                <option value="">Seleccionar empresa</option>
                                @foreach($enterprises as $ent)
                                    <option value="{{ $ent->id }}" {{ $enterprise && $enterprise->id == $ent->id ? 'selected' : '' }}>
                                        {{ $ent->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Mapeo de cursos --}}
                        <div id="course-matches-container">
                            @if($courseMatches->count())
                                <h6 class="text-muted fw-semibold small mb-2">MAPEO DE CURSOS</h6>
                                @foreach($courseMatches as $cm)
                                    <div class="mb-3 border rounded p-3 bg-light course-match-row">
                                        <div class="mb-2 d-flex align-items-center gap-2 flex-wrap">
                                            <span class="badge bg-light-secondary text-dark border">
                                                <i class="fas fa-envelope me-1"></i>{{ $cm['text'] }}
                                            </span>
                                            @if(!$cm['matched'])
                                                <span class="badge bg-warning text-white">Sin asignar</span>
                                            @else
                                                <span class="badge bg-light-success text-success">
                                                    <i class="fas fa-check me-1"></i>Asignado
                                                </span>
                                            @endif
                                        </div>
                                        <select class="form-select select2 course-select"
                                                name="course_map[{{ $cm['text'] }}]"
                                                data-course-text="{{ $cm['text'] }}"
                                                {{ $isReadOnly ? 'disabled' : '' }}>
                                            <option value="">— Seleccionar curso —</option>
                                            @foreach($enterpriseCourses as $course)
                                                <option value="{{ $course->id }}"
                                                    {{ $cm['matched'] && $cm['matched']->id == $course->id ? 'selected' : '' }}>
                                                    {{ $course->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            @else
                                <div class="alert alert-info small">
                                    <i class="fas fa-info-circle me-1"></i>
                                    No se detectaron cursos en este correo.
                                </div>
                            @endif
                        </div>

                        {{-- Guardar alias --}}
                        <div class="form-check mt-2">
                            <input type="checkbox" class="form-check-input" id="save_alias" name="save_alias" value="1">
                            <label class="form-check-label small" for="save_alias">
                                Guardar este mapeo como alias para futuros correos
                            </label>
                        </div>

                    </div>
                    @if(!$isReadOnly)
                    <div class="card-footer border-top bg-light">
                        <button type="submit" id="confirm-btn" class="btn btn-primary w-100 mb-2">
                            Confirmar y crear orden
                        </button>
                        <button type="button" id="discard-btn-form" class="btn btn-outline-secondary w-100">
                            Descartar correo
                        </button>
                    </div>
                    @endif
                </form>
            </div>

            {{-- Cuerpo del correo --}}
            @if($mail->raw_body)
                <div class="card">
                    <div class="card-header p-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h6 class="mb-0 fw-bold">Cuerpo del correo</h6>
                                <p class="text-muted">Contenido original recibido</p>
                            </div>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-primary active" id="btnFormatted">
                                    Texto
                                </button>
                                <button type="button" class="btn btn-outline-primary" id="btnRaw">
                                    Raw
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="preview-wrapper">
                            <div id="bodyFormatted" class="preview-body-formatted">
                                {!! nl2br(e($mail->raw_body)) !!}
                            </div>
                            <pre id="bodyRaw" class="preview-body-raw d-none">{{ $mail->raw_body }}</pre>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <p class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Contenido exacto del correo recibido desde {{ $mail->from }}
                        </p>
                    </div>
                </div>
            @endif

        </div>
    </div>

    {{-- Modal descartar --}}
    <div id="discard-modal" class="modal fade">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Descartar correo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="display-4 text-warning mb-2"><i class="fas fa-triangle-exclamation"></i></div>
                    <h5 class="mb-1">¿Descartar este correo?</h5>
                    <p class="text-muted small">Será marcado como ignorado y no generará una orden.</p>
                    <div class="row justify-content-center mt-3">
                        <div class="col-sm-12 col-md-6">
                            <button type="button" id="discard-confirm-btn" class="btn btn-danger w-100 mb-2">Confirmar</button>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/mails/view.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/mails/view.js') }}"></script>
@endpush
