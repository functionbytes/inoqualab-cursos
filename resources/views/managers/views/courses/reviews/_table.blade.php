<div class="card">

            {{-- Header --}}
            

            {{-- Search + Filtros --}}
            <div class="card-body border-bottom">
                @php
                    $filterChips = [];
                    if (($rating ?? '') !== '') {
                        $filterChips[] = [
                            'label' => 'Calificacion: ' . $rating . ' estrella' . ($rating == 1 ? '' : 's'),
                            'clear_url' => url()->current() . '?' . http_build_query(request()->except('rating')),
                        ];
                    }
                @endphp
                <form method="GET" action="{{ Request::url() }}" id="searchForm">

                    <input type="hidden" name="rating" id="filterRating" value="{{ $rating ?? '' }}">

                    @php ob_start(); @endphp
                <div class="filter-popover-field">
                    <div class="filter-popover-label">Calificacion</div>
                    <div class="filter-popover-options">
                        <label class="filter-popover-option">
                            <input type="radio" data-filter-name="popover_Rating" value="" {{ ($rating ?? '') === '' ? 'checked' : '' }}>
                            <span class="filter-popover-dot"></span>
                            <span>Todas las calificaciones</span>
                        </label>
                        @for($r = 5; $r >= 1; $r--)
                            <label class="filter-popover-option">
                                <input type="radio" data-filter-name="popover_Rating" value="{{ $r }}" {{ ($rating ?? '') == $r ? 'checked' : '' }}>
                                <span class="filter-popover-dot"></span>
                                <span>{{ $r }} estrella{{ $r === 1 ? '' : 's' }}</span>
                            </label>
                        @endfor
                    </div>
                </div>
                    @php $popoverBody = trim(ob_get_clean()); @endphp

                    @include('managers.includes.filter-toolbar', [
                        'searchName' => 'search',
                        'searchValue' => $searchKey ?? '',
                        'searchPlaceholder' => 'Buscar por curso, estudiante o comentario...',
                        'popoverBody' => $popoverBody,
                        'filterChips' => $filterChips,
                    ])
                </form>
            </div>

            {{-- Tabla --}}
            <div class="card-body">
                @if($reviews->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="courses-col-checkbox">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>Estudiante</th>
                                    <th class="text-center">Calificacion</th>
                                    <th>Comentario</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reviews as $review)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox"
                                                   value="{{ $review->id }}">
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ trim((optional($review->user)->firstname ?? 'Estudiante') . ' ' . (optional($review->user)->lastname ?? '')) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-warning">
                                                @for($s = 1; $s <= 5; $s++)
                                                    <i class="fa-{{ $s <= $review->rating ? 'solid' : 'regular' }} fa-star"></i>
                                                @endfor
                                            </span>
                                        </td>
                                        <td class="reviews-comment-col">
                                            <span class="text-muted">{{ $review->comment ? Str::limit($review->comment, 120) : '—' }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ \Carbon\Carbon::parse($review->created_at)->format('d/m/Y') }}</span>
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
                                                        <a class="dropdown-item btn-review-detail" href="#"
                                                           data-course="{{ optional($review->course)->title ?? 'Curso eliminado' }}"
                                                           data-student="{{ trim((optional($review->user)->firstname ?? 'Estudiante') . ' ' . (optional($review->user)->lastname ?? '')) }}"
                                                           data-rating="{{ $review->rating }}"
                                                           data-comment="{{ $review->comment ?? '' }}"
                                                           data-date="{{ \Carbon\Carbon::parse($review->created_at)->format('d/m/Y H:i') }}">
                                                            Ver detalle
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item btn-delete" href="#"
                                                           data-url="{{ route('manager.reviews.destroy', $review->id) }}"
                                                           data-title="Eliminar reseña de {{ optional($review->user)->firstname ?? 'este estudiante' }}">
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
                        <i class="fas fa-star fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">
                            @if(($searchKey ?? '') || ($rating ?? '') !== '')
                                No se encontraron resultados
                            @else
                                No hay reseñas
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if(($searchKey ?? '') || ($rating ?? '') !== '')
                                No hay reseñas que coincidan con los filtros aplicados.
                            @else
                                Las reseñas aparecerán aquí cuando los estudiantes califiquen los cursos.
                            @endif
                        </p>
                        @if(($searchKey ?? '') || ($rating ?? '') !== '')
                            <a href="{{ Request::url() }}" class="btn btn-outline-secondary">
                                Ver todas
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            @include('managers.includes.pagination-footer', [
                'paginator' => $reviews,
                'itemLabel' => 'reseñas',
            ])

        </div>
