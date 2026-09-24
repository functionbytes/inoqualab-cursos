@extends('layouts.managers')

@section('title', $course->title . ' — ' . $enterprise->title)

@section('page_header')
    @php ob_start(); @endphp
    <a href="{{ route('manager.enterprises.courses.insert', [$enterprise->slack, $course->slack]) }}"
       class="btn btn-outline-secondary btn-icon" title="Ingresar usuario" aria-label="Ingresar usuario">
        <i class="fas fa-download"></i>
    </a>
    <a href="{{ route('manager.enterprises.courses.reasign', [$enterprise->slack, $course->slack]) }}"
       class="btn btn-outline-secondary btn-icon" title="Reasignar" aria-label="Reasignar">
        <i class="fas fa-exchange-alt"></i>
    </a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => $course->title,
        'breadcrumbs' => [
            ['label' => 'Empresas', 'url' => route('manager.enterprises')],
            ['label' => $enterprise->title, 'url' => route('manager.enterprises.courses', $enterprise->slack)],
            ['label' => $course->title],
        ],
        'actions' => $headerActions,
    ])
@endsection

@section('content')


<div class="widget-content searchable-container list">

    <div id="ajax-table-root">
        @include('managers.views.enterprises.courses._view', [
            'enterprise' => $enterprise,
            'course' => $course,
            'searchKey' => $searchKey ?? null,
            'culminate' => $culminate ?? null,
            'users' => $users,
        ])
    </div>
</div>
@push('scripts')
<script src="{{ asset('managers/js/views/enterprises/courses/view.js') }}"></script>
@endpush

@endsection