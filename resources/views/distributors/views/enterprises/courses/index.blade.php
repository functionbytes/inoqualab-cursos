@extends('layouts.managers')

@section('page_header')
    @include('distributors.includes.card', ['title' => 'Cursos'])
@endsection

@section('content')

    <div class="widget-content searchable-container list"
         data-bulk-url="{{ route('distributor.enterprises.courses.bulk-action', $enterprise->slack) }}"
         data-bulk-entity-label="curso(s)">

        <div class="card card-body">
            <div class="row">
                <div class="col-md-12 col-xl-12">
                    <form class="position-relative form-search" action="{{ Request::fullUrl() }}" method="GET">
                        <div class="row justify-content-between g-2 ">
                            <div class="col-auto flex-grow-1">
                                <div class="tt-search-box">
                                    <div class="input-group">
                                        <span class="position-absolute top-50 start-0 translate-middle-y ms-2"> <i class="fas fa-magnifying-glass"></i></span>
                                        <input class="form-control rounded-start w-100 ps-5" type="text" id="search" name="search" placeholder="Buscar" @isset($searchKey) value="{{ $searchKey }}" @endisset>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Buscar">
                                    <i class="fa-duotone fa-magnifying-glass"></i>
                                </button>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('distributor.enterprises.courses.assign',$enterprise->slack) }}" class="btn btn-primary">
                                    <i class="fa-duotone fa-plus"></i>
                                </a>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card card-body">
            <div class="table-responsive">
                <table class="table search-table align-middle text-nowrap">
                    <thead class="header-item">
                    <tr>
                        <th scope="col" class="col-checkbox"><input type="checkbox" class="form-check-input" id="select-all"></th>
                        <th scope="col">Titulo</th>
                        <th scope="col">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach ($courses as $key => $course)
                        <tr class="search-items">

                            <td>
                                <input type="checkbox" class="form-check-input bulk-checkbox" value="{{ $course->id }}">
                            </td>
                            <td>
                                <span class="usr-email-addr" data-email="{{ $course->title }}">{{ Str::words( Str::upper(Str::lower($course->title)), 12, '...')  }}</span>
                            </td>
                            <td class="text-left">
                                <div class="dropdown dropstart">
                                    <a href="#" class="text-muted" id="dropdownMenuButton-{{ $loop->index }}" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-vertical fs-5"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-{{ $loop->index }}">
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-3" href="{{ route('distributor.enterprises.courses.view', [$enterprise->slack,  $course->slack]) }}">Detalle</a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            </div>
            <div class="result-body ">
                <span>Mostrar {{ $courses->firstItem() }}-{{ $courses->lastItem() }} de {{ $courses->total() }} resultados</span>
                <nav>
                    {{ $courses->appends(request()->input())->links() }}
                </nav>
            </div>
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


