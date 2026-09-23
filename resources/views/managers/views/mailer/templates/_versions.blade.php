{{--
    Partial AJAX: tabla + paginacion + modales de diff del historial de
    versiones de una plantilla de correo.

    Los modales #diffModal{id} van DENTRO de este partial (no en versions.blade.php)
    porque dependen de los IDs de $versions de la pagina actual — si vivieran
    fuera de #ajax-table-root, al paginar seguirian mostrando los de la pagina
    anterior y los botones "Ver diff" de las filas nuevas apuntarian a modales
    inexistentes.

    Se incluye normalmente desde versions.blade.php (dentro de #ajax-table-root)
    para el render inicial, y el controller devuelve ESTE mismo partial (sin
    layout) cuando la peticion es AJAX ($request->ajax()) al paginar. Ver
    public/managers/js/ajax-table.js.
--}}
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
                    Volver al editor
                </a>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        @if ($versions->isEmpty())
            <div class="text-center py-5 text-muted">
                <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-history', 48) !!}</div>
                <p class="mb-0">No hay versiones guardadas para esta plantilla aún.</p>
                <small>Las versiones se crean automáticamente al guardar cambios.</small>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 version-col-id">#</th>
                            <th>Asunto</th>
                            <th>Nota del cambio</th>
                            <th>Guardado por</th>
                            <th>Fecha</th>
                            <th class="version-col-actions"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($versions as $version)
                            <tr>
                                <td class="ps-3 text-muted">{{ $version->id }}</td>
                                <td>
                                    <span class="text-truncate d-inline-block version-subject-truncate"
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
                                            Restaurar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @include('managers.includes.pagination-footer', [
                'paginator' => $versions,
                'itemLabel' => 'versiones',
            ])
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
                        <pre class="border rounded p-3 bg-light small version-diff-pre">{{ $version->content }}</pre>
                    </div>
                </div>
                <div class="modal-footer d-block">
                    <form method="POST"
                          action="{{ route('mailers.templates.versions.restore', [$template->uid, $version->id]) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100 mb-2 btn-restore"
                                data-confirm="¿Restaurar esta versión?">
                            Restaurar esta versión
                        </button>
                    </form>
                    <button type="button" class="btn btn-light w-100" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endforeach
