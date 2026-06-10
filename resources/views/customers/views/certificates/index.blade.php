@extends('layouts.customers')

@section('content')

    @include('customers.includes.card', ['title' => 'Mis certificados'])

    <div class="widget-content searchable-container list">

        <div class="card card-body">
            <div class="table-responsive">
                <table class="table align-middle text-nowrap">
                    <thead class="header-item">
                        <tr>
                            <th>Curso</th>
                            <th>Emitido</th>
                            <th>Vence</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($certificates as $certificate)
                            @php
                                $end = $certificate->end_at ? \Carbon\Carbon::parse($certificate->end_at) : null;
                                $daysLeft = $end ? \Carbon\Carbon::now()->startOfDay()->diffInDays($end->copy()->startOfDay(), false) : null;

                                if ($daysLeft === null) {
                                    $state = ['Sin fecha', 'secondary'];
                                } elseif ($daysLeft < 0) {
                                    $state = ['Vencido', 'danger'];
                                } elseif ($daysLeft <= 30) {
                                    $state = ['Por vencer', 'warning'];
                                } else {
                                    $state = ['Vigente', 'success'];
                                }
                                $needsRenewal = $daysLeft !== null && $daysLeft <= 30;
                                $courseSlack = optional($certificate->course)->slack;
                            @endphp
                            <tr>
                                <td>
                                    <span class="fw-semibold">{{ optional($certificate->course)->title ?? 'Curso' }}</span>
                                </td>
                                <td>{{ $certificate->start_at ? \Carbon\Carbon::parse($certificate->start_at)->format('d/m/Y') : '—' }}</td>
                                <td>{{ $end ? $end->format('d/m/Y') : '—' }}</td>
                                <td>
                                    <span class="badge bg-light-{{ $state[1] }} text-{{ $state[1] }} rounded-3 py-2 px-3 fw-semibold">
                                        {{ $state[0] }}
                                        @if($needsRenewal && $daysLeft >= 0)
                                            <small class="text-muted">({{ (int) $daysLeft }} días)</small>
                                        @endif
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown dropstart">
                                        <a href="#" class="text-muted" id="certMenu{{ $certificate->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-vertical"></i>
                                        </a>
                                        <ul class="dropdown-menu" aria-labelledby="certMenu{{ $certificate->id }}">
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center gap-3" href="{{ route('customers.certificate.download', $certificate->slack) }}" target="_blank">
                                                    <i class="fa-duotone fa-file-arrow-down"></i> Descargar
                                                </a>
                                            </li>
                                            @if($needsRenewal && $courseSlack)
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center gap-3 text-primary" href="{{ route('checkout', ['course', $courseSlack]) }}">
                                                        <i class="fa-duotone fa-rotate"></i> Renovar
                                                    </a>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="fa-duotone fa-award fs-1 d-block mb-2"></i>
                                    Aún no tienes certificados. Completa un curso y aprueba su examen para obtenerlo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="result-body">
            <span>Mostrar {{ $certificates->firstItem() }}-{{ $certificates->lastItem() }} de {{ $certificates->total() }} resultados</span>
            <nav>{{ $certificates->appends(request()->input())->links() }}</nav>
        </div>
    </div>
@endsection
