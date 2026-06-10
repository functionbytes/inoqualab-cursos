@php use Carbon\Carbon; @endphp
@extends('layouts.customers')

@section('title', 'Cursos')

@section('content')
<div class="container-fluid note-has-grid">

    @include('customers.includes.card', ['title' => 'Cursos'])

    {{-- Filtros --}}
    <ul class="nav nav-pills p-3 mb-3 rounded align-items-center card flex-row">
        <li class="nav-item">
            <a href="javascript:void(0)" id="order-all" class="nav-link note-link d-flex align-items-center justify-content-center px-3 me-2 text-body-color active">
                <span class="d-md-block font-weight-medium">TODOS</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="javascript:void(0)" id="order-earrings" class="nav-link note-link d-flex align-items-center justify-content-center px-3 me-2 text-body-color">
                <span class="d-md-block font-weight-medium">PENDIENTES</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="javascript:void(0)" id="order-expires" class="nav-link note-link d-flex align-items-center justify-content-center px-3 me-2 text-body-color">
                <span class="d-md-block font-weight-medium">EXPIRADOS</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="javascript:void(0)" id="order-completeds" class="nav-link note-link d-flex align-items-center justify-content-center px-3 me-2 text-body-color">
                <span class="d-md-block font-weight-medium">COMPLETADOS</span>
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <div id="note-full-container" class="note-has-grid row g-3">
            @foreach($courses as $course)
                @php
                    $progress   = min(100, round($course->percent, 2));
                    $culminated = $course->culminated;
                    $certificate = $culminated == 1 ? $course->certificate : null;
                    $expire     = Carbon::parse($course->enroll_expire);
                    $diff       = max(0, (int) floor(Carbon::now()->diffInDays($expire, false)));
                    $isExpired  = $diff <= 0 && $culminated == 0;
                    $thumb      = $course->course?->getFirstMedia('thumbnail');
                    $thumbUrl   = $thumb ? $thumb->getFullUrl() : asset('/pages/images/courses/default.jpg');
                    $year       = date('Y', strtotime($course->enroll_start));
                @endphp

                <div class="col-xl-4 col-md-6 col-sm-12 single-note-item order-all
                    {{ $isExpired ? 'order-expires' : '' }}
                    {{ $culminated == 0 && !$isExpired ? 'order-earrings' : '' }}
                    {{ $culminated == 1 ? 'order-completeds' : '' }}">

                    <div class="card h-100 overflow-hidden border-0 shadow-sm">

                        {{-- Thumbnail --}}
                        <div class="position-relative" style="aspect-ratio:16/9;overflow:hidden;background:#0d1b2a;">
                            <img src="{{ $thumbUrl }}"
                                 alt="{{ $course->course?->title }}"
                                 style="width:100%;height:100%;object-fit:cover;display:block;"
                                 onerror="this.src='{{ asset('/pages/images/courses/default.jpg') }}'">
                            <span class="badge position-absolute top-0 start-0 m-2
                                @if($culminated == 1) bg-success
                                @elseif($isExpired) bg-danger
                                @else bg-primary
                                @endif" style="font-size:11px;padding:5px 10px;">
                                @if($culminated == 1) Completado
                                @elseif($isExpired) Expirado
                                @else En curso
                                @endif
                            </span>
                            <span class="badge bg-dark bg-opacity-75 position-absolute top-0 end-0 m-2" style="font-size:11px;padding:5px 10px;">{{ $year }}</span>
                        </div>

                        <div class="card-body d-flex flex-column gap-2 p-3">

                            <h6 class="fw-semibold mb-0 lh-sm" style="font-size:13.5px;">
                                <a class="text-dark text-decoration-none"
                                   href="{{ route('customers.courses.content', $course->slack) }}">
                                    {{ $course->course?->title }}
                                </a>
                            </h6>

                            {{-- Progreso --}}
                            <div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span style="font-size:11.5px;color:#5a7093;">Progreso</span>
                                    <span style="font-size:11.5px;font-weight:700;color:#0d1b2a;">{{ $progress }}%</span>
                                </div>
                                <div class="progress" style="height:6px;border-radius:4px;">
                                    <div class="progress-bar {{ $culminated == 1 ? 'bg-success' : 'bg-primary' }}"
                                         style="width:{{ $progress }}%"></div>
                                </div>
                            </div>

                            {{-- Días restantes --}}
                            @if($culminated == 0)
                                <div class="d-flex align-items-center gap-1" style="font-size:12px;color:{{ $isExpired ? '#dc3545' : ($diff <= 10 ? '#fd7e14' : '#5a7093') }};">
                                    <i class="fa-solid fa-clock" style="font-size:11px;"></i>
                                    @if($isExpired)
                                        Acceso expirado
                                    @elseif($diff <= 10)
                                        Vence en {{ $diff }} {{ $diff == 1 ? 'día' : 'días' }}
                                    @else
                                        {{ $diff }} días restantes
                                    @endif
                                </div>
                            @endif

                            {{-- CTA --}}
                            <div class="mt-auto pt-1 d-flex gap-2">
                                @if($culminated == 1 && $certificate)
                                    <a class="btn btn-sm btn-outline-success flex-fill"
                                       href="{{ route('customers.certificate.download', $certificate->slack) }}"
                                       target="_blank">
                                        <i class="fa-solid fa-award me-1"></i> Certificado
                                    </a>
                                @endif
                                <a class="btn btn-sm flex-fill {{ $isExpired ? 'btn-outline-secondary' : 'btn-primary' }}"
                                   href="{{ route('customers.courses.content', $course->slack) }}">
                                    <i class="fa-solid fa-{{ $culminated == 1 ? 'rotate-right' : ($isExpired ? 'lock' : 'play') }} me-1"></i>
                                    {{ $culminated == 1 ? 'Repasar' : ($isExpired ? 'Ver detalle' : 'Continuar') }}
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ url('managers/js/apps/notes.js') }}"></script>
@endpush
