@php use Carbon\Carbon; @endphp

@extends('layouts.customers')

@section('title', 'Dashboard')

@section('content')
    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">
            <div class="card w-100 content-dashboard">
                <div class="position-relative">
                    <div class="row">
                        <div class="col-sm-7">
                            <div class="mb-7 mt-6">
                                <h2 class="fw-semibold mb-1 text-uppercase">Bienvenido!</h2>
                                <p class="text-black">Aquí encontrarás un resumen detallado de tus cursos pendientes,
                                    diseñado para que puedas seguir tu progreso educativo de manera fácil y efectiva.
                                    Desde esta plataforma intuitiva, podrás explorar y gestionar tus cursos de manera
                                    eficiente, manteniéndote al día con tus metas de aprendizaje.</p>
                            </div>
                        </div>
                        <div class="col-sm-5">
                            <div class="welcome-bg-img mb-n7 text-end">
                                <img src="/customers/images/dashboard/dashboard.svg" alt="" class="img-fluid">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @php
        $totalCursos = $courses->count();
        $completados = $courses->where('culminated', 1)->count();
        $certificados = $courses->filter(fn ($c) => $c->certificate)->count();
        $porVencer = $courses->filter(function ($c) {
            $end = optional($c->certificate)->end_at;
            if (! $end) {
                return false;
            }
            $days = Carbon::now()->startOfDay()->diffInDays(Carbon::parse($end)->startOfDay(), false);

            return $days >= 0 && $days <= 30;
        })->count();
    @endphp

    <div class="row">
        @php $stats = [
            ['Cursos', $totalCursos, 'fa-ballot-check', 'primary'],
            ['Completados', $completados, 'fa-circle-check', 'success'],
            ['Certificados', $certificados, 'fa-award', 'info'],
            ['Por vencer', $porVencer, 'fa-clock', 'warning'],
        ]; @endphp
        @foreach($stats as [$label, $value, $icon, $color])
            <div class="col-6 col-xl-3">
                <div class="card w-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-light-{{ $color }} text-{{ $color }}" style="width:48px;height:48px;font-size:20px;">
                            <i class="fa-duotone {{ $icon }}"></i>
                        </span>
                        <div>
                            <h3 class="mb-0 fw-semibold">{{ $value }}</h3>
                            <span class="fs-3 text-muted">{{ $label }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if(count($courses) > 0)
        <div class="row">
            <div class="col-xl-12 d-flex align-items-strech">
                <div class="card w-100">
                    <div class="card-body ">
                        <div class="owl-carousel collectibles-carousel owl-theme ">
                            @foreach($courses as $course)

                                @php
                                    $course->percent  >= 100 ? $progress = 100 : $progress = round($course->percent, 2);
                                    $culminated = $course->culminated;
                                    $culminated == 1 ? $certificate = $course->certificate : $certificate = null;
                                    $enroll = Carbon::now();
                                    $expire = Carbon::parse($course->enroll_expire);
                                    $diff = max(0, (int) floor($enroll->diffInDays($expire, false)));
                                @endphp

                                @php
                                    $isExpired = $diff <= 0 && $culminated == 0;
                                    $thumb = $course->course?->getFirstMedia('thumbnail');
                                    $thumbUrl = $thumb ? $thumb->getFullUrl() : asset('/pages/images/courses/default.jpg');
                                @endphp
                                <div class="card overflow-hidden border-0 shadow-sm">
                                    <div class="position-relative" style="aspect-ratio:16/9;overflow:hidden;background:#0d1b2a;">
                                        <img src="{{ $thumbUrl }}" alt="{{ $course->course?->title }}"
                                             style="width:100%;height:100%;object-fit:cover;display:block;"
                                             onerror="this.src='{{ asset('/pages/images/courses/default.jpg') }}'">
                                        <span class="badge position-absolute top-0 start-0 m-2
                                            {{ $culminated == 1 ? 'bg-success' : ($isExpired ? 'bg-danger' : 'bg-primary') }}"
                                            style="font-size:10px;padding:4px 8px;">
                                            {{ $culminated == 1 ? 'Completado' : ($isExpired ? 'Expirado' : 'En curso') }}
                                        </span>
                                    </div>
                                    <div class="card-body p-3 d-flex flex-column gap-2">
                                        <h6 class="fw-semibold mb-0 lh-sm" style="font-size:13px;">
                                            <a class="text-dark text-decoration-none" href="{{ route('customers.courses.content', $course->slack) }}">
                                                {{ $course->course?->title }}
                                            </a>
                                        </h6>
                                        <div>
                                            <div class="d-flex justify-content-between mb-1">
                                                <span style="font-size:11px;color:#5a7093;">Progreso</span>
                                                <span style="font-size:11px;font-weight:700;">{{ $progress }}%</span>
                                            </div>
                                            <div class="progress" style="height:5px;border-radius:4px;">
                                                <div class="progress-bar {{ $culminated == 1 ? 'bg-success' : 'bg-primary' }}" style="width:{{ $progress }}%"></div>
                                            </div>
                                        </div>
                                        <a class="btn btn-sm w-100 {{ $isExpired ? 'btn-outline-secondary' : 'btn-primary' }}"
                                           href="{{ route('customers.courses.content', $course->slack) }}">
                                            <i class="fa-solid fa-{{ $culminated == 1 ? 'rotate-right' : ($isExpired ? 'lock' : 'play') }} me-1"></i>
                                            {{ $culminated == 1 ? 'Repasar' : ($isExpired ? 'Ver detalle' : 'Continuar') }}
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
@endsection


@push('scripts')

    <script src="{{ url('/managers/libs/owl.carousel/dist/owl.carousel.min.js') }}" type="text/javascript"></script>

    <script type="text/javascript">

        $(document).ready(function () {


            $(".collectibles-carousel").owlCarousel({
                loop: false,
                margin: 30,
                mouseDrag: true,
                autoplay: true,
                autoplayTimeout: 4000,
                autoplaySpeed: 2000,
                nav: false,
                dots: false,
                rtl: false,
                responsive: {
                    0: {
                        items: 1,
                    },
                    576: {
                        items: 2,
                    },
                    768: {
                        items: 2,
                    },
                    1280: {
                        items: 3,
                    },
                },
            });


        });

    </script>

@endpush
