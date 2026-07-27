@extends('layouts.managers')

@section('content')

    @include('supports.includes.card', ['title' => 'Actividades - ' . $user->firstname . ' ' . $user->lastname])

    <div class="widget-content searchable-container list">

        <div class="card card-body">
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <span class="fw-semibold">Filtrar por modelo:</span>
                </div>
                @foreach($models as $key => $label)
                    <div class="col-auto">
                        <a href="{{ Request::fullUrlWithQuery(['model' => $key]) }}"
                           class="btn btn-sm {{ $model == $key ? 'btn-primary' : 'btn-outline-secondary' }}">
                            {{ $label }}
                            @if(isset($counts[$key]))
                                <span class="badge bg-light text-dark ms-1">{{ $counts[$key] }}</span>
                            @endif
                        </a>
                    </div>
                @endforeach
                @if($model)
                    <div class="col-auto">
                        <a href="{{ Request::fullUrlWithQuery(['model' => null]) }}" class="btn btn-sm btn-light">
                            Todos
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <div class="card card-body">
            <div class="table-responsive">
                <table class="table search-table align-middle text-nowrap">
                    <thead class="header-item">
                        <tr>
                            <th scope="col">Descripción</th>
                            <th scope="col">Modelo</th>
                            <th scope="col">Fecha</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $activity)
                            <tr class="search-items">
                                <td>
                                    <span>{{ $activity->description }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light-primary rounded-3 py-2 text-primary fw-semibold fs-2 d-inline-flex align-items-center gap-1">
                                        {{ $activity->subject_type ? class_basename($activity->subject_type) : 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <span>{{ $activity->created_at->format('d/m/Y H:i') }}</span>
                                </td>
                                <td>
                                    <div class="dropdown dropstart">
                                        <a href="#" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-vertical fs-5"></i>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center gap-3" href="{{ route('support.users.activitys.view', $activity->id) }}">
                                                    Ver detalle
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Sin actividades registradas</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($activities->total() > 0)
                <div class="result-body">
                    <span>{{ $activities->total() }} resultado(s)</span>
                </div>
                {{ $activities->links() }}
            @endif
        </div>

    </div>

@endsection
