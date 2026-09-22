@extends('layouts.managers')

@section('title', $course->title . ' — ' . $enterprise->title)

@section('page_header')
    @include('managers.includes.card', [
        'title' => $course->title,
        'breadcrumbs' => [
            ['label' => 'Empresas', 'url' => route('manager.enterprises')],
            ['label' => $enterprise->title, 'url' => route('manager.enterprises.courses', $enterprise->slack)],
            ['label' => $course->title],
        ],
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

@endsection§§   §1§1    Q   1