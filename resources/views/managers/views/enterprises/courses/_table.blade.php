<div class="card">

            {{-- Header --}}
            

            {{-- Search --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('manager.enterprises.courses', $enterprise->slack) }}" id="searchForm">
                    @include('managers.includes.filter-toolbar', [
                        'searchName' => 'search',
                        'searchValue' => $searchKey ?? '',
                        'searchPlaceholder' => 'Buscar por título...',
                    ])
                </form>
            </div>

            {{-- Table --}}
            <div class="card-body">
                @if($courses->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="col-checkbox">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>Título</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($courses as $course)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox"
                                                   value="{{ $course->id }}">
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ Str::words($course->title, 12, '...') }}</div>
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
                                                        <a class="dropdown-item"
                                                           href="{{ route('manager.enterprises.courses.view', [$enterprise->slack, $course->slack]) }}">
                                                            Dashboard
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item btn-delete" href="#"
                                                           data-url="{{ route('manager.enterprises.courses.destroy', [$enterprise->slack, $course->slack]) }}"
                                                           data-title="Eliminar: {{ $course->title }}">
                                                            Eliminar
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-courses', 48) !!}</div>
                        <h5 class="fw-bold mb-2">
                            @if($searchKey ?? '')
                                No se encontraron resultados
                            @else
                                No hay cursos asignados
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if($searchKey ?? '')
                                No hay cursos que coincidan con la búsqueda.
                            @else
                                Agrega el primer curso a esta empresa.
                            @endif
                        </p>
                        @if($searchKey ?? '')
                            <a href="{{ route('manager.enterprises.courses', $enterprise->slack) }}" class="btn btn-outline-secondary">
                                Ver todos
                            </a>
                        @else
                            <a href="{{ route('manager.enterprises.courses.create', $enterprise->slack) }}" class="btn btn-primary">
                                Agregar curso
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            @include('managers.includes.pagination-footer', [
                'paginator' => $courses,
                'itemLabel' => 'cursos',
            ])

        </div>
