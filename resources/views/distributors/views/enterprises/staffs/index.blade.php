@extends('layouts.managers')

@section('content')


    <div class="widget-content searchable-container list"
         data-bulk-url="{{ route('distributor.enterprises.staffs.bulk-action', $enterprise->slack) }}"
         data-bulk-entity-label="empleado(s)">

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
                                <div class="input-group">
                                    <select class="form-select select2" name="available" data-minimum-results-for-search="Infinity">
                                        <option value="">Seleccionar estado</option>
                                        <option value="1" @isset($available) @if ($available==1) selected @endif @endisset>  Activo</option>
                                        <option value="0" @isset($available) @if ($available==0) selected  @endif @endisset>  Inactivo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Buscar">
                                    <i class="fa-duotone fa-magnifying-glass"></i>
                                </button>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('distributor.enterprises.staffs.create', $enterprise->slack) }}" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Crear">
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
                        <th scope="col">Identificación</th>
                        <th scope="col">Cliente</th>
                        <th scope="col">Correo electronico</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Fecha</th>
                        <th scope="col">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach ($users as $key => $user)
                        <tr class="search-items">

                            <td>
                                <input type="checkbox" class="form-check-input bulk-checkbox" value="{{ $user->id }}">
                            </td>
                            <td>
                                <span class="usr-email-addr" data-email="{{ $user->identification }}">{{ ucfirst($user->identification) }}</span>
                            </td>
                            <td>
                                <span class="usr-email-addr" data-email="{{ $user->firstname . ' ' . $user->lastname }}">{{ Str::words( Str::upper(Str::lower($user->firstname . ' ' . $user->lastname)), 12, '...')  }}</span>
                            </td>
                            <td>
                                <span class="usr-email-addr" data-email="{{ $user->email }}">{{ $user->email }}</span>
                            </td>
                            <td>
                              <span class="badge {{ $user->available == 1 ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} rounded-3 py-2 fw-semibold fs-2 d-inline-flex align-items-center gap-1">

                                   {{ $user->available == 1 ? 'Activo' : 'Inactivo' }}
                              </span>
                            </td>
                            <td>
                                <span class="usr-ph-no" data-phone="{{ date('Y-m-d', strtotime($user->updated_at)) }}">{{ date('Y-m-d', strtotime($user->updated_at)) }}</span>
                            </td>
                            <td class="text-left">
                                <div class="dropdown dropstart">
                                    <a href="#" class="text-muted" id="dropdownMenuButton-{{ $loop->index }}" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-vertical fs-5"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-{{ $loop->index }}">
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-3" href="{{ route('distributor.enterprises.staffs.edit', $user->slack) }}">
                                                Editar
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-3 confirm-delete" data-href="{{ route('distributor.enterprises.staffs.destroy', $user->slack) }}">Eliminar</a>
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
                <span>Mostrar {{ $users->firstItem() }}-{{ $users->lastItem() }} de {{ $users->total() }} resultados</span>
                <nav>
                    {{ $users->appends(request()->input())->links() }}
                </nav>
            </div>
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'empleado(s)',
        'bulkActions' => [
            ['value' => 'activate', 'label' => 'Activar'],
            ['value' => 'deactivate', 'label' => 'Desactivar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('distributors/css/tables.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('distributors/js/enterprises/staffs/index.js') }}"></script>
@endpush


