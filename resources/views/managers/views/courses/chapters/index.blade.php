@extends('layouts.managers')

@section('content')

    @include('managers.includes.card', ['title' => 'Temas'])

    <div class="widget-content searchable-container list">
        <div class="card card-body">
            <div class="row">
                <div class="col-md-12 col-xl-12">
                    <form class="position-relative form-search" action="{{ Request::fullUrl() }}" method="GET">
                        <div class="row justify-content-between g-2 ">
                            <div class="col-auto flex-grow-1">
                                <div class="tt-search-box">
                                    <div class="input-group">
                                        <span class="position-absolute top-50 start-0 translate-middle-y ms-2"> <i data-feather="search"></i></span>
                                        <input class="form-control rounded-start w-100" type="text" id="search" name="search" placeholder="Buscar" @isset($searchKey) value="{{ $searchKey }}" @endisset>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="input-group">
                                    <select class="form-select select2" name="available" data-minimum-results-for-search="Infinity">
                                        <option value="">Seleccionar estado</option>
                                        <option value="1" @isset($available) @if ($available==1) selected @endif @endisset>  Publico</option>
                                        <option value="0" @isset($available) @if ($available==0) selected  @endif @endisset>  Oculto</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Buscar">
                                    <i class="fas fa-magnifying-glass"></i>
                                </button>
                            </div>
                            <div class="col-auto">
                                <a href=" {{ route('manager.courses.chapters.create', $course->slack) }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i>
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
                            <th>Titulo</th>
                            <th>Posición</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>

                    @foreach ($chapters as $key => $chapter)
                        <tr class="search-items">
                            <td>
                                <span class="usr-email-addr" data-email="{{ $chapter->title }}">{{ Str::upper( Str::lower($chapter->title) )  }}</span>
                            </td>
                            <td>
                                <span class="usr-email-addr" data-email="{{ $chapter->position }}">{{ $chapter->position }}</span>
                            </td>
                            <td>
                              <span class="badge {{ $chapter->available == 1 ? 'bg-light-primary text-primary' : 'bg-light-secondary text-secondary' }} rounded-3 py-2 fw-semibold fs-2 d-inline-flex align-items-center gap-1">
                                   {{ $chapter->available == 1 ? 'Publico' : 'Oculto' }}
                              </span>
                            </td>
                            <td>
                                <span class="usr-ph-no" data-phone="{{ date('Y-m-d', strtotime($chapter->updated_at)) }}">{{ date('Y-m-d', strtotime($chapter->updated_at)) }}</span>
                            </td>
                            <td class="text-left">
                                <div class="dropdown dropstart">
                                    <a href="#" class="text-muted" id="dropdown-{{ $chapter->slack }}" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-vertical fs-5"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="dropdown-{{ $chapter->slack }}">

                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-3" href="{{ route('manager.courses.chapters.edit', $chapter->slack) }}">Editar</a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-3 confirm-delete" data-href="{{ route('manager.courses.chapters.destroy', $chapter->slack) }}">Eliminar</a>
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
                <span>Mostrar {{ $chapters->firstItem() }}-{{ $chapters->lastItem() }} de {{ $chapters->total() }} resultados</span>
                <nav>
                    {{ $chapters->appends(request()->input())->links() }}
                </nav>
            </div>
        </div>
    </div>
@endsection




