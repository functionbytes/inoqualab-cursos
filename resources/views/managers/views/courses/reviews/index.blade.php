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
            <div class="modal-content review-modal-content">
                <div class="review-modal-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="review-avatar" id="review-detail-avatar"></div>
                        <div>
                            <div class="review-modal-name" id="review-detail-student"></div>
                            <div class="review-modal-course" id="review-detail-course"></div>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white review-modal-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="review-rating-row">
                        <span id="review-detail-rating" class="reviews-stars reviews-stars-lg"></span>
                        <span class="review-modal-date" id="review-detail-date"></span>
                    </div>

                    <div class="review-comment-card">
                        <i class="fas fa-quote-left review-comment-icon"></i>
                        <p class="mb-0" id="review-detail-comment"></p>
                    </div>

                    <div class="review-visibility-row" id="review-visibility-row">
                        <div>
                            <div class="review-visibility-label">Visibilidad publica</div>
                            <div class="text-muted small" id="review-visibility-hint">Se muestra en la pagina del curso</div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input review-visibility-switch" type="checkbox" role="switch" id="review-visibility-switch">
                        </div>
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
