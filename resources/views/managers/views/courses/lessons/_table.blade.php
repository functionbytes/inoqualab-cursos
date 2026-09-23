<div class="card">

            {{-- Header --}}
            

            {{-- Search + Filtros --}}
            <div class="card-body border-bottom">
                @php
                    $filterChips = [];
                    if (($available ?? '') !== '') {
                        $__popoverLabels_Available = ['1' => 'Público', '0' => 'Oculto'];
                        $filterChips[] = [
                            'label' => 'Estado: ' . ($__popoverLabels_Available[$available ?? ''] ?? ($available ?? '')),
                            'clear_url' => url()->current() . '?' . http_build_query(request()->except('available')),
                        ];
                    }
                    if (($chapter ?? '') !== '') {
                        $__popoverLabels_Chapter = $chapters->pluck('title', 'id');
                        $filterChips[] = [
                            'label' => 'Módulo: ' . ($__popoverLabels_Chapter[$chapter ?? ''] ?? ($chapter ?? '')),
                            'clear_url' => url()->current() . '?' . http_build_query(request()->except('chapter')),
                        ];
                    }
                    if (($type ?? '') !== '') {
                        $__popoverLabels_Type = $types->pluck('title', 'id');
                        $filterChips[] = [
                            'label' => 'Tipo: ' . ($__popoverLabels_Type[$type ?? ''] ?? ($type ?? '')),
                            'clear_url' => url()->current() . '?' . http_build_query(request()->except('type')),
                        ];
                    }
                @endphp
                <form method="GET" action="{{ Request::fullUrl() }}" id="searchForm">

                    <input type="hidden" name="available" id="filterAvailable" value="{{ $available ?? '' }}">
                    <input type="hidden" name="chapter"   id="filterChapter"   value="{{ $chapter ?? '' }}">
                    <input type="hidden" name="type"      id="filterType"      value="{{ $type ?? '' }}">

                    @php
                        $activeFilters = (int)(($available ?? '') !== '') + (int)(($chapter ?? '') !== '') + (int)(($type ?? '') !== '');
                    @endphp
                    @php ob_start(); @endphp
                <div class="filter-popover-field">
                    <div class="filter-popover-label">Estado</div>
                    <div class="filter-popover-options">
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Available" value="" {{ ($available ?? '') === '' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Todos</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Available" value="1" {{ ($available ?? '') === '1' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Público</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Available" value="0" {{ ($available ?? '') === '0' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Oculto</span>
                    </label>
                    </div>
                </div>
                <div class="filter-popover-field">
                    <div class="filter-popover-label">Módulo</div>
                    <div class="filter-popover-options">
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Chapter" value="" {{ ($chapter ?? '') === '' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Todos</span>
                    </label>
                    @foreach($chapters as $item)
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Chapter" value="{{ $item->id }}" {{ (isset($chapter) && $chapter == $item->id) ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>{{ $item->title }}</span>
                    </label>
                    @endforeach
                    </div>
                </div>
                <div class="filter-popover-field">
                    <div class="filter-popover-label">Tipo</div>
                    <div class="filter-popover-options">
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Type" value="" {{ ($type ?? '') === '' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Todos</span>
                    </label>
                    @foreach($types as $item)
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Type" value="{{ $item->id }}" {{ (isset($type) && $type == $item->id) ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>{{ $item->title }}</span>
                    </label>
                    @endforeach
                    </div>
                </div>
@php $popoverBody = trim(ob_get_clean()); @endphp

                    @include('managers.includes.filter-toolbar', [
                        'searchName' => 'search',
                        'searchValue' => $searchKey ?? '',
                        'searchPlaceholder' => 'Buscar por título...',
                        'popoverBody' => $popoverBody,
                        'filterChips' => $filterChips,
                    ])
                </form>
            </div>

            {{-- Tabla --}}
            <div class="card-body">
                @if($lessons->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="lessons-col-handle"></th>
                                    <th class="text-center"><input type="checkbox" class="form-check-input" id="select-all"></th>
                                    <th>Título</th>
                                    <th class="text-center">Posición</th>
                                    <th>Módulo</th>
                                    <th class="text-center">Tipo</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Actualización</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="lessons-sortable" data-reorder-url="{{ route('manager.courses.lessons.reorder') }}">
                                @foreach($lessons as $lesson)
                                    @php
                                        $lessonTitle = Str::title(Str::lower($lesson->title));
                                        $chapterTitle = Str::title(Str::lower($lesson->chapter->title));
                                    @endphp
                                    <tr data-id="{{ $lesson->id }}">
                                        <td class="text-center lessons-col-handle">
                                            <i class="fas fa-bars text-muted drag-handle" title="Arrastra para reordenar"></i>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" class="form-check-input bulk-checkbox" value="{{ $lesson->id }}">
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-truncate d-inline-block lessons-title-truncate" title="{{ $lessonTitle }}">
                                                {{ $lessonTitle }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ $lesson->position }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted text-truncate d-inline-block lessons-title-truncate" title="{{ $chapterTitle }}">
                                                {{ $chapterTitle }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary-subtle text-secondary">{{ Str::ucfirst(Str::lower($lesson->type->title)) }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($lesson->available)
                                                <span class="badge bg-success-subtle text-success">Público</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Oculto</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ $lesson->updated_at->format('d/m/Y') }}</span>
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
                                                        <a class="dropdown-item btn-edit-lesson" href="#"
                                                           data-slack="{{ $lesson->slack }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item btn-delete" href="#"
                                                           data-url="{{ route('manager.courses.lessons.destroy', $lesson->slack) }}"
                                                           data-title="Eliminar: {{ $lessonTitle }}">
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
                            @if($searchKey || $activeFilters > 0)
                                No se encontraron resultados
                            @else
                                No hay clases
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if($searchKey || $activeFilters > 0)
                                No hay clases que coincidan con los filtros aplicados.
                            @else
                                Crea la primera clase de este curso.
                            @endif
                        </p>
                        @if($searchKey || $activeFilters > 0)
                            <a href="{{ route('manager.courses.lessons', $course->slack) }}" class="btn btn-outline-secondary">
                                Ver todas
                            </a>
                        @else
                            <button type="button" class="btn btn-primary btn-icon btn-new-lesson"
                                    data-bs-toggle="modal" data-bs-target="#lesson-modal"
                                    title="Nueva clase" aria-label="Nueva clase">{!! \App\Html\IconHelper::render('plus') !!}</button>
                        @endif
                    </div>
                @endif
            </div>

            @include('managers.includes.pagination-footer', [
                'paginator' => $lessons,
                'itemLabel' => 'clases',
            ])

        </div>
