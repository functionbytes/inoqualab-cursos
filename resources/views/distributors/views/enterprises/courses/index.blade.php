@extends('layouts.managers')

@section('page_header')
    @php ob_start(); @endphp
    <a href="{{ route('distributor.enterprises.courses.assign', $enterprise->slack) }}" class="btn btn-primary btn-icon" title="Asignar curso" aria-label="Asignar curso">{!! \App\Html\IconHelper::render('plus') !!}</a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('distributors.includes.card', [
        'title' => 'Cursos',
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list"
         data-bulk-url="{{ route('distributor.enterprises.courses.bulk-action', $enterprise->slack) }}"
         data-bulk-entity-label="curso(s)">

        <div class="card">

            {{-- Search --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('distributor.enterprises.courses', $enterprise->slack) }}" id="searchForm">
                    @include('managers.includes.filter-toolbar', [
                        'searchName' => 'search',
                        'searchValue' => $searchKey ?? '',
                        'searchPlaceholder' => 'Buscar por titulo...',
                    ])
                </form>
            </div>

            {{-- Tabla --}}
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-nowrap mb-0">
                        <thead class="table-light">
                        <tr>
                            <th class="col-checkbox"><input type="checkbox" class="form-check-input" id="select-all"></th>
                            <th>Titulo</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach ($courses as $course)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input bulk-checkbox" value="{{ $course->id }}">
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ Str::words(Str::upper(Str::lower($course->title)), 12, '...') }}</div>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0"
                                                data-bs-toggle="dropdown"
                                                data-bs-boundary="viewport">
                                            <i class="fas fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('distributor.enterprises.courses.view', [$enterprise->slack, $course->slack]) }}">Detalle</a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>
            </div>

            @include('managers.includes.pagination-footer', [
                'paginator' => $courses,
                'itemLabel' => 'cursos',
            ])
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'curso(s)',
        'bulkActions' => [
            ['value' => 'delete', 'label' => 'Quitar de la empresa'],
        ],
    ])
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('distributors/css/tables.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('distributors/js/enterprises/courses/index.js') }}"></script>
@endpush


