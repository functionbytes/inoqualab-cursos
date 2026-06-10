@extends('layouts.supports')

@section('content')

    @include('supports.includes.card', ['title' => 'Revisar correo entrante'])

    @if($mail->status === 'failed' && $mail->error_log)
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3">
            <i class="fa-duotone fa-circle-exclamation"></i>
            <div><strong>Error al procesar:</strong> {{ $mail->error_log }}</div>
        </div>
    @endif

    <div class="row g-3">

        {{-- Columna izquierda: datos del correo --}}
        <div class="col-12 col-lg-5">
            <div class="card">
                <div class="card-body border-top">
                    <h5 class="mb-3">Datos del correo</h5>

                    <div class="mb-2">
                        <label class="control-label col-form-label fw-semibold">Remitente</label>
                        <input type="text" class="form-control" value="{{ $mail->from }}" disabled>
                    </div>
                    <div class="mb-2">
                        <label class="control-label col-form-label fw-semibold">Asunto</label>
                        <input type="text" class="form-control" value="{{ $mail->subject }}" disabled>
                    </div>
                    <div class="mb-2">
                        <label class="control-label col-form-label fw-semibold">Recibido</label>
                        <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($mail->received_at)->format('Y-m-d H:i') }}" disabled>
                    </div>

                    @if(!empty($payload))
                        <hr>
                        <h6 class="fw-semibold mb-3">Datos extraídos del correo</h6>

                        @if(!empty($payload['document']))
                            <div class="mb-2">
                                <label class="control-label col-form-label fw-semibold">Documento</label>
                                <input type="text" class="form-control" value="{{ $payload['document'] }}" disabled>
                            </div>
                        @endif

                        @if(!empty($payload['name']))
                            <div class="mb-2">
                                <label class="control-label col-form-label fw-semibold">Nombre</label>
                                <input type="text" class="form-control" value="{{ $payload['name'] }}" disabled>
                            </div>
                        @endif

                        @if(!empty($payload['enterprise_raw']) || !empty($payload['enterprise_code']))
                            <div class="mb-2">
                                <label class="control-label col-form-label fw-semibold">Empresa detectada</label>
                                <input type="text" class="form-control" value="{{ $payload['enterprise_raw'] ?? '' }}{{ !empty($payload['enterprise_code']) ? ' (' . $payload['enterprise_code'] . ')' : '' }}" disabled>
                            </div>
                        @endif

                        @if(!empty($payload['enterprise_name']))
                            <div class="mb-2">
                                <label class="control-label col-form-label fw-semibold">Nombre empresa</label>
                                <input type="text" class="form-control" value="{{ $payload['enterprise_name'] }}" disabled>
                            </div>
                        @endif

                        @if(!empty($payload['ips']))
                            <div class="mb-2">
                                <label class="control-label col-form-label fw-semibold">IPS</label>
                                <input type="text" class="form-control" value="{{ is_array($payload['ips']) ? implode(', ', $payload['ips']) : $payload['ips'] }}" disabled>
                            </div>
                        @endif

                        @if(!empty($payload['courses']))
                            <div class="mb-3">
                                <label class="control-label col-form-label fw-semibold">Cursos en el correo</label>
                                <ul class="list-group list-group-flush">
                                    @foreach($payload['courses'] as $courseText)
                                        <li class="list-group-item px-0">
                                            <i class="fa-duotone fa-book-open me-2 text-primary"></i>
                                            {{ $courseText }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @endif

                    {{-- Cuerpo raw colapsable --}}
                    @if($mail->raw_body)
                        <details class="mt-3">
                            <summary class="fw-semibold text-muted" style="cursor:pointer">Ver cuerpo original</summary>
                            <pre class="mt-2 p-3 bg-light rounded" style="white-space: pre-wrap; font-size: 0.8rem; max-height: 300px; overflow-y: auto;">{{ $mail->raw_body }}</pre>
                        </details>
                    @endif
                </div>
            </div>
        </div>

        {{-- Columna derecha: confirmación de orden --}}
        <div class="col-12 col-lg-7">
            <div class="card">
                <form id="confirmForm" onsubmit="return false">
                    <div class="card-body border-top">
                        <h5 class="mb-3">Confirmar orden</h5>

                        {{-- Empresa --}}
                        <div class="mb-3">
                            <label class="control-label col-form-label fw-semibold" for="enterprise_id">Empresa</label>
                            <select class="form-select select2" id="enterprise_id" name="enterprise_id">
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
                                <label class="control-label col-form-label fw-semibold">Mapeo de cursos</label>
                                @foreach($courseMatches as $cm)
                                    <div class="mb-3 border rounded p-3 course-match-row">
                                        <div class="mb-1">
                                            <span class="badge bg-light-secondary text-dark">
                                                <i class="fa-duotone fa-envelope me-1"></i>{{ $cm['text'] }}
                                            </span>
                                            @if(!$cm['matched'])
                                                <span class="badge bg-warning ms-1">Sin asignar</span>
                                            @endif
                                        </div>
                                        <select class="form-select select2 course-select"
                                                name="course_map[{{ $cm['text'] }}]"
                                                data-course-text="{{ $cm['text'] }}">
                                            <option value="">-- Seleccionar curso --</option>
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
                                <div class="alert alert-info">No se detectaron cursos en este correo.</div>
                            @endif
                        </div>

                        {{-- Guardar alias --}}
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="save_alias" name="save_alias" value="1">
                            <label class="form-check-label" for="save_alias">Guardar este mapeo para futuros correos</label>
                        </div>

                    </div>
                    <div class="card-footer border-top">
                        <button type="submit" id="confirm-btn" class="btn btn-primary w-100 mb-2">
                            <i class="fa-duotone fa-circle-check me-1"></i> Confirmar y crear orden
                        </button>
                        <button type="button" id="discard-btn" class="btn btn-outline-secondary w-100">
                            Descartar
                        </button>
                    </div>
                </form>
            </div>
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
                    <div class="display-4 text-warning"><i class="fa-duotone fa-triangle-exclamation"></i></div>
                    <h4 class="my-0">¿Descartar este correo?</h4>
                    <p>El correo será marcado como ignorado y no generará una orden.</p>
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

@push('scripts')
<script type="text/javascript">
$(document).ready(function () {

    // Init select2 para empresa y cursos
    $('#enterprise_id').select2({ placeholder: 'Seleccionar empresa', allowClear: true });
    initCourseSelects();

    function initCourseSelects() {
        $('.course-select').each(function () {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                $(this).select2({ placeholder: 'Seleccionar curso', allowClear: true });
            }
        });
    }

    // Cambio de empresa → recargar opciones de curso
    $('#enterprise_id').on('change', function () {
        var enterpriseId = $(this).val();
        if (!enterpriseId) return;

        $.ajax({
            url: '{{ route("support.mails.courses") }}',
            method: 'GET',
            data: { enterprise_id: enterpriseId },
            success: function (courses) {
                $('.course-select').each(function () {
                    var $select = $(this);
                    $select.select2('destroy');
                    $select.empty().append('<option value="">-- Seleccionar curso --</option>');
                    $.each(courses, function (i, course) {
                        $select.append($('<option>', { value: course.id, text: course.text }));
                    });
                    $select.select2({ placeholder: 'Seleccionar curso', allowClear: true });
                });
            },
            error: function () {
                toastr.error('Error al cargar los cursos.', 'Error', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
            }
        });
    });

    // Submit confirmar orden
    $('#confirmForm').on('submit', function () {
        var enterpriseId = $('#enterprise_id').val();

        if (!enterpriseId) {
            toastr.warning('Debe seleccionar una empresa.', 'Advertencia', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
            return false;
        }

        var courseMap = {};
        $('.course-select').each(function () {
            var text = $(this).data('course-text');
            var val = $(this).val();
            if (val) courseMap[text] = val;
        });

        var payload = {
            enterprise_id: enterpriseId,
            course_map: courseMap,
            save_alias: $('#save_alias').is(':checked') ? 1 : 0
        };

        $('#confirm-btn').prop('disabled', true).text('Procesando...');

        $.ajax({
            url: '{{ route("support.mails.confirm", $mail->slack) }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            contentType: 'application/json',
            data: JSON.stringify(payload),
            success: function (response) {
                $('#confirm-btn').prop('disabled', false).text('Confirmar y crear orden');
                if (response.success) {
                    toastr.success(response.message, 'Listo', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
                    setTimeout(function () {
                        window.location.href = '{{ route("support.mails.index") }}';
                    }, 1200);
                } else {
                    toastr.error(response.message, 'Error', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
                }
            },
            error: function (xhr) {
                $('#confirm-btn').prop('disabled', false).text('Confirmar y crear orden');
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    $.each(xhr.responseJSON.errors, function (field, messages) {
                        toastr.warning(messages[0], 'Validación', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
                    });
                } else {
                    toastr.error('Error al procesar la solicitud.', 'Error', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
                }
            }
        });
    });

    // Botón descartar
    $('#discard-btn').on('click', function () {
        $('#discard-modal').modal('show');
    });

    $('#discard-confirm-btn').on('click', function () {
        $.ajax({
            url: '{{ route("support.mails.discard", $mail->slack) }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                $('#discard-modal').modal('hide');
                if (response.success) {
                    toastr.success(response.message, 'Listo', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
                    setTimeout(function () {
                        window.location.href = '{{ route("support.mails.index") }}';
                    }, 1200);
                } else {
                    toastr.error(response.message, 'Error', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
                }
            },
            error: function () {
                $('#discard-modal').modal('hide');
                toastr.error('Error al procesar la solicitud.', 'Error', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
            }
        });
    });

});
</script>
@endpush
