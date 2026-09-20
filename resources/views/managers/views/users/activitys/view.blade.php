@extends('layouts.managers')

@section('content')

    @include('managers.includes.card', ['title' => 'Detalle actividad'])

    <div class="row g-3">

        <div class="col-12 col-lg-8">
            <div class="card card-body">
                <h6 class="fw-bold mb-3 border-bottom pb-2">Información general</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <p class="mb-1 fs-2 text-muted">Descripción</p>
                        <h6 class="fw-semibold mb-0">{{ $activity->description }}</h6>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1 fs-2 text-muted">Evento</p>
                        <h6 class="fw-semibold mb-0">{{ $activity->event ?? 'N/A' }}</h6>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1 fs-2 text-muted">Modelo afectado</p>
                        <h6 class="fw-semibold mb-0">
                            @if($activity->subject_type)
                                {{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}
                            @else
                                N/A
                            @endif
                        </h6>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1 fs-2 text-muted">Fecha</p>
                        <h6 class="fw-semibold mb-0">{{ $activity->created_at->format('d/m/Y H:i:s') }}</h6>
                    </div>
                    @if($activity->causer)
                        <div class="col-md-6">
                            <p class="mb-1 fs-2 text-muted">Usuario que realizó el cambio</p>
                            <h6 class="fw-semibold mb-0">{{ $activity->causer->name ?? $activity->causer->firstname.' '.$activity->causer->lastname }}</h6>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card card-body">
                <a href="{{ url()->previous() }}" class="btn btn-light w-100">
                    Volver
                </a>
            </div>
        </div>

        @if($activity->properties && $activity->properties->isNotEmpty())
            @php
                $oldData = $activity->properties->get('old');
                $newData = $activity->properties->get('attributes');
            @endphp

            @if($oldData || $newData)
                <div class="col-12">
                    <div class="card card-body">
                        <h6 class="fw-bold mb-3 border-bottom pb-2">Cambios registrados</h6>
                        <div class="row g-3">
                            @if($oldData)
                                <div class="col-md-6">
                                    <h6 class="fw-semibold text-danger mb-2">Antes</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Campo</th>
                                                    <th scope="col">Valor</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($oldData as $field => $value)
                                                    <tr>
                                                        <td class="fw-semibold">{{ $field }}</td>
                                                        <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                            @if($newData)
                                <div class="col-md-6">
                                    <h6 class="fw-semibold text-success mb-2">Después</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Campo</th>
                                                    <th scope="col">Valor</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($newData as $field => $value)
                                                    <tr>
                                                        <td class="fw-semibold">{{ $field }}</td>
                                                        <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        @endif

    </div>

@endsection
