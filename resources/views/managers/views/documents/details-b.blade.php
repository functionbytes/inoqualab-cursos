{{-- details · diseño B — contenido compartido por todos los perfiles.
     Página: managers.views.documents.page. Enlaces en $links (details/view). --}}
@php
    $F = \App\Html\DocumentFormat::class;
    $tone = $F::tone($invoice->condition_id);
    $groups = $F::enterpriseGroups($details);
    $detailTotal = $groups->sum('total');
    $seats = $groups->sum('seats');
    $topValue = (float) ($groups->first()['total'] ?? 0);
@endphp
    <div class="row g-4 align-items-start">

        <div class="col-lg-8">
            <div class="card mb-0">
                <div class="card-header border-bottom">
                    <h6 class="mb-1 fw-bold">Empresas del distribuidor</h6>
                    <p class="text-muted small mb-0">
                        Ordenadas de mayor a menor valor. Abre una empresa para ver sus cursos e inscripciones.
                    </p>
                </div>
                @forelse($groups as $group)
                    <details class="doc-b-group" @if($loop->first) open @endif>
                        <summary>
                            <div class="min-w-0">
                                <div class="doc-b-group-name">{{ $group['name'] }}</div>
                                <div class="doc-b-group-meta">
                                    {{ $group['courses']->count() }} {{ $group['courses']->count() === 1 ? 'curso' : 'cursos' }},
                                    {{ number_format($group['seats'], 0, ',', '.') }} inscripciones
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <strong class="doc-num">{{ $F::money($group['total']) }}</strong>
                                <span class="doc-b-chevron" aria-hidden="true">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6" /></svg>
                                </span>
                            </div>
                        </summary>
                        <div class="doc-b-group-body">
                            <table>
                                <tbody>
                                    @foreach($group['courses'] as $course)
                                        <tr>
                                            <td>{{ $course['course'] }}</td>
                                            <td class="text-end text-muted doc-num">{{ ceil($course['quantity']) }}</td>
                                            <td class="text-end fw-semibold doc-num">{{ $F::money($course['totalAmount']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </details>
                @empty
                    <div class="card-body">
                        <p class="repeater-empty text-muted small mb-0">
                            Esta factura no tiene inscripciones asociadas a empresas, así que no hay reparto que mostrar.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="col-lg-4">
            <div class="doc-b-aside">

                <div class="card">
                    <div class="card-body">
                        <img src="{{ getlogo() }}" alt="{{ config('app.name') }}" class="doc-b-logo">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="doc-b-total-label">Total de la factura</div>
                            <span class="badge {{ $tone === 'paid' ? 'bg-success-subtle text-success' : ($tone === 'rejected' ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary') }}">{{ $invoice->condition?->title }}</span>
                        </div>
                        <div class="doc-b-total doc-num">{{ $F::money($invoice->total_invoices_amount) }}</div>
                        <ul class="doc-b-lines">
                            <li><span>Distribuidor</span><strong class="text-end">{{ $invoice->distributor->title ?? '—' }}</strong></li>
                            <li><span>Suma del reparto</span><strong class="doc-num">{{ $F::money($detailTotal) }}</strong></li>
                            <li><span>Empresas</span><strong class="doc-num">{{ $groups->count() }}</strong></li>
                            <li><span>Inscripciones</span><strong class="doc-num">{{ number_format($seats, 0, ',', '.') }}</strong></li>
                        </ul>
                    </div>
                </div>

                @if($groups->isNotEmpty())
                    <div class="card mb-0">
                        <div class="card-header border-bottom">
                            <h6 class="mb-0 fw-bold">Empresas con más valor</h6>
                        </div>
                        <div class="card-body">
                            <ul class="doc-b-share">
                                @foreach($groups->take(5) as $group)
                                    <li>
                                        <div class="doc-b-share-top">
                                            <span class="doc-b-share-name" title="{{ $group['name'] }}">{{ $group['name'] }}</span>
                                            <strong class="doc-num">{{ $F::percent($detailTotal > 0 ? $group['total'] / $detailTotal * 100 : 0, 0) }}</strong>
                                        </div>
                                        <div class="doc-bar"><span data-width="{{ $topValue > 0 ? round($group['total'] / $topValue * 100, 1) : 0 }}"></span></div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

            </div>
        </div>

    </div>
