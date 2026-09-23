@extends('layouts.managers')

@section('title', 'Historial de auditorías — ' . ($seoMeta->title ?? 'Meta SEO'))

@section('page_header')
    @include('managers.includes.card', ['title' => 'Historial de auditorías — ' . ($seoMeta->title ?? 'Meta SEO')])
@endsection

@section('content')


    {{-- Cabecera con info del meta --}}
    <div class="card mb-4" data-flash-success="{{ session('success') }}">
        <div class="card-body d-flex align-items-center gap-3">
            <div class="flex-grow-1">
                <h6 class="fw-bold mb-1">{{ $seoMeta->title ?? '(sin título)' }}</h6>
                @if($seoMeta->canonical_url)
                    <a href="{{ $seoMeta->canonical_url }}" target="_blank" class="text-muted small">
                        {{ $seoMeta->canonical_url }}
                    </a>
                @endif
                <div class="mt-1">
                    @if($seoMeta->seo_grade)
                        @php
                            $gradeColor = match($seoMeta->seo_grade) {
                                'A' => 'success', 'B' => 'primary', 'C' => 'warning',
                                default => 'danger'
                            };
                        @endphp
                        <span class="badge bg-{{ $gradeColor }}-subtle text-{{ $gradeColor }} me-1">
                            Grade {{ $seoMeta->seo_grade }}
                        </span>
                    @endif
                    @if($seoMeta->seo_score !== null)
                        <span class="badge bg-secondary-subtle text-secondary">
                            Score: {{ $seoMeta->seo_score }}/100
                        </span>
                    @endif
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('manager.seo.metas.edit', $seoMeta) }}" class="btn btn-outline-primary btn-sm">
                    Editar meta SEO
                </a>
                <a href="{{ route('manager.seo.audit.history') }}" class="btn btn-light btn-sm">
                    Volver al historial
                </a>
            </div>
        </div>
    </div>

    {{-- Tabla de auditorías --}}
    <div class="card">
        <div class="card-header border-bottom p-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">
                Auditorías registradas
                <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $logs->total() }}</span>
            </h6>
            <a href="{{ route('manager.seo.audit.index') }}" class="btn btn-outline-primary btn-sm">
                Nueva auditoría
            </a>
        </div>

        @if($logs->isEmpty())
            <div class="card-body text-center py-5">
                <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-search', 48) !!}</div>
                <h6 class="text-muted">Sin auditorías registradas</h6>
                <p class="text-muted mb-3">Ejecuta una auditoría para ver el historial de esta página.</p>
                <a href="{{ route('manager.seo.audit.index') }}" class="btn btn-primary btn-sm">
                    Ir a auditoría SEO
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th class="text-center">Score</th>
                            <th class="text-center">Grade</th>
                            <th class="text-center">Issues</th>
                            <th class="text-center">Aprobados</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            @php
                                $gradeColor = match($log->grade) {
                                    'A' => 'success', 'B' => 'primary', 'C' => 'warning',
                                    default => 'danger'
                                };
                            @endphp
                            <tr>
                                <td>
                                    <span class="fw-semibold">{{ $log->audited_at->format('d/m/Y H:i') }}</span>
                                    <br>
                                    <p class="text-muted">{{ $log->audited_at->diffForHumans() }}</p>
                                </td>
                                <td class="text-center">
                                    <strong class="text-{{ $gradeColor }}">{{ $log->score }}</strong>
                                    <p class="text-muted">/100</p>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $gradeColor }}-subtle text-{{ $gradeColor }} fw-bold">
                                        {{ $log->grade }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($log->issues_count > 0)
                                        <span class="badge bg-danger-subtle text-danger">
                                            {{ $log->issues_count }}
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary-subtle text-secondary">
                                        {{ $log->passed_count }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    @if($log->issues_count > 0)
                                        <button type="button"
                                                class="btn btn-sm btn-outline-secondary btn-view-issues"
                                                data-issues="{{ json_encode($log->issues) }}">
                                            Ver issues
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @include('managers.includes.pagination-footer', [
                'paginator' => $logs,
                'itemLabel' => 'registros',
            ])
        @endif
    </div>

    {{-- Modal para ver issues de una auditoría específica --}}
    <div class="modal fade" id="issuesModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Issues de la auditoría</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="issuesModalBody">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light w-100" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="{{ asset('managers/js/flash-toastr.js') }}"></script>
<script src="{{ asset('managers/js/views/seo/audit/meta-history.js') }}"></script>
@endpush

