@extends('layouts.customers')
@section('title', "$course->title")
@section('head')
    @php
        $url = URL::current();
    @endphp
    <meta name="title" content="{{ $course->title }}">
    <meta name="description" content="{{ $course->short_detail }} ">
    <meta property="og:title" content="{{ $course->title }} ">
    <meta property="og:url" content="{{ $url }}">
    <meta property="og:description" content="{{ $course->short_detail }}">
    <meta property="og:image" content="{{ asset('images/course/' . $course->preview_image) }}">
    <meta itemprop="image" content="{{ asset('images/course/' . $course->preview_image) }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ asset('images/course/' . $course->preview_image) }}">
    <meta property="twitter:title" content="{{ $course->title }} ">
    <meta property="twitter:description" content="{{ $course->short_detail }}">
    <meta name="twitter:site" content="{{ url()->full() }}" />
    <link rel="canonical" href="{{ url()->full() }}" />
    <meta name="robots" content="all">
@endsection
@push('css')
    <link rel="stylesheet" href="{{ url('/customers/css/aula.css') }}">
@endpush
@section('content')
@if(setting('aula_version') == '2')
    <div class="lv">
        <div class="lv-shell">
            @include('customers.partials.views.courses.rail')
            <main class="lv-main">
                <div class="lv-content">
                    <div class="lv-quiz">
                        <div class="quiz-wrap">
                            @include('customers.partials.views.quizs.quiz-questions')
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
@else
    <div class="aula">
        <div class="aula-grid">
            <div class="lesson-panel">
                <div class="lp-body">
                    <div class="quiz-wrap">
                        @include('customers.partials.views.quizs.quiz-questions')
                    </div>
                </div>
            </div>
            @include('customers.partials.views.courses.rail')
        </div>
    </div>
@endif

@include('customers.partials.views.courses.assessment-exit-guard')

{{-- Mismo patrón visual que assessment-exit-guard (.ax-modal): antes era un
     modal Bootstrap genérico con botones en línea, distinto a los demás
     modales del aula, lo que junto con el diálogo nativo del navegador
     (ver fix en assessment-exit-guard.blade.php) generaba confusión sobre
     cuál modal era cuál. --}}
<div class="modal fade" id="quizConfirmModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content ax-modal">
            <div class="modal-body">
                <div class="ax-ico" aria-hidden="true">
                    @include('customers.includes.icon', ['name' => 'send'])
                </div>
                <h5 class="ax-title">¿Finalizar y enviar tus respuestas?</h5>
                <p class="ax-text">No podrás cambiarlas después de enviarlas.</p>
                <div class="ax-actions">
                    <button type="button" class="ax-btn ax-accent" id="quizConfirmAccept">Sí, enviar respuestas</button>
                    <button type="button" class="ax-btn ax-leave" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
    .quiz-native-input { position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; }
    .quiz-foot.lv-foot { padding: 26px 0 0; margin-top: 10px; max-width: 100%; }
</style>
@endpush

@push('scripts')
    <script type="text/javascript">
        var totalques = 0;

        $(document).ready(function() {

            totalques = $('.quiz-step').length;

            var i = 1;
            var count = 0;

            // Marca visualmente la opción seleccionada (radio: única selección; checkbox: múltiple)
            $(document).on('change', '.quiz-native-input', function() {
                var $input = $(this);
                var $opt = $input.closest('.quiz-opt');
                if ($input.attr('type') === 'radio') {
                    $opt.closest('.quiz-options').find('.quiz-opt').removeClass('sel');
                }
                $opt.toggleClass('sel', $input.is(':checked'));
                $('#quizAnswerError').hide();
            });

            // D1: confirmar el envío (modal propio, no el confirm() nativo del navegador) y evitar dobles envíos.
            // D2: envío por AJAX -- se queda en la misma URL (/quiz/{id})
            // mostrando el resultado (antes navegaba a customers.quiz.finish).
            // Un refresh posterior vuelve a mostrar las preguntas, no el
            // resultado -- es la contrapartida aceptada de no navegar nunca.
            var quizConfirmed = false;
            var quizConfirmModal = new bootstrap.Modal(document.getElementById('quizConfirmModal'));
            $('#question-form').on('submit', function(e) {
                // Con el envío ahora por AJAX el submit nativo nunca debe llegar
                // a ocurrir de verdad: se previene siempre, y solo el fallback
                // de error de red lo vuelve a disparar explícitamente (ver abajo).
                e.preventDefault();
                if (quizConfirmed) { return; }
                quizConfirmModal.show();
            });
            $('#quizConfirmAccept').on('click', function() {
                if (quizConfirmed) { return; }
                quizConfirmed = true;
                quizConfirmModal.hide();
                if (window.releaseAssessmentGuard) { window.releaseAssessmentGuard(); }
                $('#next').css('pointer-events', 'none').html('<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" style="animation:spin .8s linear infinite;vertical-align:-2px"><path d="M12 3a9 9 0 1 0 9 9"/></svg>');

                var $form = $('#question-form');
                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: $form.serialize(),
                    success: function(response) {
                        if (response && response.success && response.html) {
                            // Se queda en esta misma URL: no hay navegación ni
                            // cambio de URL alguno. quiz-result.blade.php ya trae
                            // su propio wrapper .aula-result (y el .ar-foot fuera
                            // de él) -- no envolver de nuevo aquí.
                            $('.quiz-wrap').html(response.html);
                            $('html, body').animate({scrollTop: $('.quiz-wrap').offset().top - 100}, 300);
                        } else {
                            // Respuesta inesperada: fallback al envío real de siempre.
                            $form.off('submit').trigger('submit');
                        }
                    },
                    error: function() {
                        // Fallo de red/servidor: fallback al envío real de siempre
                        // (deja de prevenir el submit y lo vuelve a disparar).
                        $form.off('submit').trigger('submit');
                    }
                });
            });

            $('#next').click(function() {

                var totalques = $('.quiz-step').length;
                var type = $('#type').val();
                var x = $('#next').val();
                var y = $('#prev').val();

                if (type == 0) {

                    var numberNotChecked = $('#more_quiz' + count).find('input[type="radio"]:checked').length;

                    if (numberNotChecked > 0) {

                        $('#quizAnswerError').hide();

                        i++;
                        x++;

                        $('#prev').show();

                        if (x < totalques) {

                            var z = x - 1;

                            $('#more_quiz' + x).show('fast');
                            $('#more_quiz' + z).hide('fast');
                            $('#next').val(x);
                            $('#prev').val(x);

                            if (i == totalques) {
                                $('#next').attr('type', 'submit');
                            }

                        }

                        if (x == totalques) {
                            $('#question-form').submit();
                        }

                        progres = (x / totalques) * 100;
                        $('#progressbar').css('width', progres + '%');

                        count++;

                    } else {
                        $('#quizAnswerError').show();
                    }

                }

                if (type == 1) {

                    $('#prev').show();

                    var numberNotChecked = $('#more_quiz' + count).find('input:checkbox:not(":checked")').length;

                    if (numberNotChecked != 4) {

                        $('#quizAnswerError').hide();

                        i++;
                        x++;

                        $('#prev').show();

                        if (x < totalques) {

                            var z = x - 1;

                            $('#more_quiz' + x).show('fast');
                            $('#more_quiz' + z).hide('fast');
                            $('#next').val(x);
                            $('#prev').val(x);

                            if (i == totalques) {
                                $('#next').attr('type', 'submit');
                            }

                        }

                        if (x == totalques)
                            $('#question-form').submit();

                        progres = (x / totalques) * 100;
                        $('#progressbar').css('width', progres + '%');

                        count++;
                    } else {
                        $('#quizAnswerError').show();
                    }

                }

                if (x == 1) {
                    $('#prev').show();
                }

            });

            $('#prev').click(function() {

                $('#quizAnswerError').hide();

                i--;
                count--;

                var totalques = $('.quiz-step').length;
                var x = $('#next').val();
                var y = $('#prev').val();

                $('#next').removeAttr('type');

                $('#next').show();

                y--;

                if (y == 0) {
                    $('#next').val(0);
                    $('#prev').val(1);
                    $('#prev').hide();
                } else {
                    $('#next').val(y);
                    $('#prev').val(y);
                }

                $('#more_quiz' + y).show('fast');
                $('#more_quiz' + x).hide();

                progres = (x / totalques) * 100;
                $('#progressbar').css('width', progres + '%');

            });

            // Quiz de una sola pregunta: "Finalizar" envía directo (no hay botón "siguiente").
            $('#finish').click(function() {
                if ($('#more_quiz0').find('input:checked').length > 0) {
                    $('#quizAnswerError').hide();
                    $('#question-form').submit();
                } else {
                    $('#quizAnswerError').show();
                }
            });

        });
    </script>
@endpush
