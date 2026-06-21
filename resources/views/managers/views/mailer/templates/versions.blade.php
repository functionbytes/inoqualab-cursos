@extends('layouts.managers')

@section('title', 'Historial de versiones: ' . $template->name)

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fs-4 me-2"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle fs-4 me-2"></i>
                <div>{{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header border-bottom p-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-0 fw-bold">Versiones guardadas</h5>
                    <p class="text-muted">Cada vez que guardas el template se crea una versión anterior</p>
                </div>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <a href="{{ route('mailers.templates.edit', $template->uid) }}"
                       class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Volver al editor
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            @if ($versions->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-history fa-3x mb-3 opacity-50"></i>
                    <p class="mb-0">No hay versiones guardadas para esta plantilla aún.</p>
                    <small>Las versiones se crean automáticamente al guardar cambios.</small>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width: 60px;">#</th>
                                <th>Asunto</th>
                                <th>Nota del cambio</th>
                                <th>Guardado por</th>
                                <th>Fecha</th>
                                <th style="width: 160px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($versions as $version)
                                <tr>
                                    <td class="ps-3 text-muted">{{ $version->id }}</td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 250px;"
                                              title="{{ $version->subject }}">
                                            {{ $version->subject ?: '(sin asunto)' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($version->change_note)
                                            <span class="text-muted">{{ $version->change_note }}</span>
                                        @else
                                            <span class="text-muted fst-italic">Sin nota</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($version->author)
                                            <span class="small">{{ $version->author->name }}</span>
                                        @else
                                            <span class="text-muted fst-italic">Sistema</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="small text-muted" title="{{ $version->created_at->format('d/m/Y H:i:s') }}">
                                            {{ $version->created_at->diffForHumans() }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <button type="button"
                                                class="btn btn-outline-secondary btn-sm me-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#diffModal{{ $version->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <form method="POST"
                                              action="{{ route('mailers.templates.versions.restore', [$template->uid, $version->id]) }}"
                                              class="d-inline restore-form">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-primary btn-sm btn-restore"
                                                    data-confirm="¿Restaurar esta versión? Se guardará el contenido actual antes de restaurar.">
                                                <i class="fas fa-undo me-1"></i>Restaurar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($versions->hasPages())
                    <div class="d-flex justify-content-end p-3">
                        {{ $versions->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Diff modals --}}
    @foreach ($versions as $version)
        <div class="modal fade" id="diffModal{{ $version->id }}" tabindex="-1">
            <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Versión #{{ $version->id }}
                            <small class="text-muted ms-2">{{ $version->created_at->format('d/m/Y H:i') }}</small>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted text-uppercase">Asunto</label>
                            <div class="border rounded p-2 bg-light">{{ $version->subject ?: '(sin asunto)' }}</div>
                        </div>
                        <div>
                            <label class="form-label fw-semibold text-muted text-uppercase">Contenido</label>
                            <pre class="border rounded p-3 bg-light small" style="max-height: 500px; overflow: auto; white-space: pre-wrap; word-break: break-all;">{{ $version->content }}</pre>
                        </div>
                    </div>
                    <div class="modal-footer d-block">
                        <form method="POST"
                              action="{{ route('mailers.templates.versions.restore', [$template->uid, $version->id]) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100 mb-2 btn-restore"
                                    data-confirm="¿Restaurar esta versión?">
                                <i class="fas fa-undo me-1"></i>Restaurar esta versión
                            </button>
                        </form>
                        <button type="button" class="btn btn-light w-100" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Confirm restore modal --}}
    <div class="modal fade" id="confirm-restore-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar restauración</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted" id="confirm-restore-message">¿Restaurar esta versión?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning w-100 mb-2" id="confirm-restore-btn">Restaurar</button>
                    <button type="button" class="btn btn-light w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {
    let $pendingRestoreForm = null;

    $(document).on('click', '.btn-restore', function (e) {
        e.preventDefault();
        const msg = $(this).data('confirm') || '¿Restaurar esta versión?';
        $pendingRestoreForm = $(this).closest('form');
        $('#confirm-restore-message').text(msg);
        new bootstrap.Modal(document.getElementById('confirm-restore-modal')).show();
    });

    $('#confirm-restore-btn').on('click', function () {
        if ($pendingRestoreForm) {
            $pendingRestoreForm.submit();
        }
    });
});
</script>
@endpush
