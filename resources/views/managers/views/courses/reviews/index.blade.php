@extends('layouts.managers')

@section('content')

  @include('managers.includes.card', ['title' => 'Reseñas de cursos'])

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
                    <input class="form-control rounded-start w-100" type="text" id="search" name="search" placeholder="Buscar por curso, estudiante o comentario" @isset($searchKey) value="{{ $searchKey }}" @endisset>
                  </div>
                </div>
              </div>
              <div class="col-auto">
                <div class="input-group">
                  <select class="form-select select2" name="rating" data-minimum-results-for-search="Infinity">
                    <option value="">Todas las calificaciones</option>
                    @for ($r = 5; $r >= 1; $r--)
                      <option value="{{ $r }}" @isset($rating) @if ($rating == $r) selected @endif @endisset>{{ $r }} estrella{{ $r == 1 ? '' : 's' }}</option>
                    @endfor
                  </select>
                </div>
              </div>
              <div class="col-auto">
                <button type="submit" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Buscar">
                  <i class="fa-duotone fa-magnifying-glass"></i>
                </button>
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
                <th>Curso</th>
                <th>Estudiante</th>
                <th>Calificación</th>
                <th>Comentario</th>
                <th>Fecha</th>
                <th>Acciones</th>
              </tr>
          </thead>
          <tbody>
              @forelse ($reviews as $review)
                <tr class="search-items">
                  <td>
                    <span class="usr-email-addr">{{ Str::words(Str::upper(optional($review->course)->title ?? 'Curso eliminado'), 8, '...') }}</span>
                  </td>
                  <td>
                    <span>{{ Str::upper(Str::lower(trim((optional($review->user)->firstname ?? 'Estudiante') . ' ' . (optional($review->user)->lastname ?? '')))) }}</span>
                  </td>
                  <td>
                    <span class="text-warning fw-semibold">
                      @for ($s = 1; $s <= 5; $s++)
                        <i class="fa-{{ $s <= $review->rating ? 'solid' : 'regular' }} fa-star fs-2"></i>
                      @endfor
                    </span>
                  </td>
                  <td style="white-space: normal; max-width: 360px;">
                    <span class="text-muted">{{ $review->comment ? Str::limit($review->comment, 120) : '—' }}</span>
                  </td>
                  <td>
                    <span class="usr-ph-no">{{ date('Y-m-d', strtotime($review->created_at)) }}</span>
                  </td>
                  <td class="text-left">
                    <div class="dropdown dropstart">
                      <a href="#" class="text-muted" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-dots fs-5"></i>
                      </a>
                      <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <li>
                          <a class="dropdown-item d-flex align-items-center gap-3 confirm-delete" data-href="{{ route('manager.reviews.destroy', $review->id) }}">Eliminar</a>
                        </li>
                      </ul>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center text-muted py-4">No hay reseñas registradas.</td>
                </tr>
              @endforelse
          </tbody>
        </table>
      </div>
      @if ($reviews->total() > 0)
      <div class="result-body ">
        <span>Mostrar {{ $reviews->firstItem() }}-{{ $reviews->lastItem() }} de {{ $reviews->total() }} resultados</span>
        <nav>
          {{ $reviews->appends(request()->input())->links() }}
        </nav>
      </div>
      @endif
    </div>
  </div>
@endsection
