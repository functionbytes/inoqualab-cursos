@extends('layouts.managers')

@section('title', 'Reseñas de cursos')

@section('page_header')
    @include('managers.includes.card', [
        'title' => 'Reseñas de cursos',
        'description' => 'Gestiona las reseñas y calificaciones de los estudiantes',
    ])
@endsection

@section('content')


    <div class="widget-content searchable-container list" id="courses-reviews-index"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-config='@json([
            "routes" => [
                "bulkAction" => route("manager.reviews.bulk-action"),
            ],
         ])'>

                <div id="ajax-table-root">
            @include('managers.views.courses.reviews._table')
        </div>
    </div>

    {{-- Detalle de la reseña --}}
    <div class="modal fade" id="review-detail-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalle de la reseña</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold d-block">Curso</label>
                        <span id="review-detail-course" class="text-muted"></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold d-block">Estudiante</label>
                        <span id="review-detail-student" class="text-muted"></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold d-block">Calificacion</label>
                        <span id="review-detail-rating" class="text-warning"></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold d-block">Comentario</label>
                        <span id="review-detail-comment" class="text-muted"></span>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold d-block">Fecha</label>
                        <span id="review-detail-date" class="text-muted"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'reseña(s)',
        'bulkActions' => [
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    @include('managers.includes.delete')

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/courses/reviews/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/courses/reviews/index.js') }}"></script>
@endpush
