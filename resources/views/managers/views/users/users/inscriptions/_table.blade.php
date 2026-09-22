<div class="card">

            {{-- Header --}}
            

            {{-- Search --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ Request::url() }}" id="searchForm">
                    <div class="d-flex gap-2 align-items-center">
                        <div class="flex-fill">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="search" name="search" class="form-control border-start-0 ps-0"
                                       placeholder="Buscar por curso..."
                                       value="{{ $searchKey ?? '' }}">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary flex-shrink-0">
                            <i class="fas fa-search"></i>
                        </button>
                        @if(!empty($searchKey))
                            <a href="{{ Request::url() }}" class="btn btn-outline-secondary flex-shrink-0">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Tabla --}}
            <div class="card-body">
                @if($inscriptions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Curso</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Año</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($inscriptions as $inscription)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold">{{ Str::words($inscription->course->title, 12, '...') }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($inscription->culminated == 1)
                                                <span class="badge bg-success-subtle text-success">Culminado</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Pendiente</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ date('Y', strtotime($inscription->enroll_start)) }}</span>
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
                                                           href="{{ route('manager.users.inscriptions.edit', $inscription->slack) }}">
                                                            Editar
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
                        <h5 class="fw-bold mb-2">
                            @if(!empty($searchKey))
                                No se encontraron resultados
                            @else
                                Sin inscripciones
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if(!empty($searchKey))
                                No hay inscripciones que coincidan con la búsqueda.
                            @else
                                Este usuario no tiene cursos inscritos aún.
                            @endif
                        </p>
                        @if(!empty($searchKey))
                            <a href="{{ Request::url() }}" class="btn btn-outline-secondary">
                                Ver todas
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            @include('managers.includes.pagination-footer', [
                'paginator' => $inscriptions,
                'itemLabel' => 'inscripciones',
            ])

        </div>
