@extends('layouts.managers')

@section('title', 'Resultado examenes')

@section('page_header')
    @include('managers.includes.card', [
        'title' => 'Resultado examenes',
        'description' => 'Consulta y descarga los resultados de examenes por curso',
    ])
@endsection

@section('content')


    <div class="widget-content searchable-container list" id="exams-results-index"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}">

                <div id="ajax-table-root">
            @include('managers.views.exams.results._table')
        </div>
    </div>

    

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/exams/results/index.js') }}"></script>
@endpush
