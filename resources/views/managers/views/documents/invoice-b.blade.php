{{-- invoice · diseño B — contenido compartido por todos los perfiles.
     Página: managers.views.documents.page. Enlaces en $links (details/view). --}}
@php
    $F = \App\Html\DocumentFormat::class;
    $tone = $F::tone($invoice->condition_id);
    $distributor = $invoice->distributor;
    $seats = $invoice->items->sum('quantity');
@endphp
    <div class="row g-4 align-items-start">

        <div class="col-lg-8">

            <div class="card">
                <div class="card-header border-bottom">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <h6 class="mb-1 fw-bold">Cursos facturados</h6>
                            <p class="text-muted small mb-0">{{ $invoice->items->count() }} cursos y {{ number_format($seats, 0, ',', '.') }} inscripciones en el periodo.</p>
                        </div>
@if(! empty($links['details']))
                        <a href="{{ $links['details'] }}" class="btn btn-icon btn-actions-icon flex-shrink-0" title="Ver reparto por empresa" aria-label="Ver reparto por empresa">{!! \App\Html\IconHelper::render('nav-enterprises') !!}</a>
@endif
                    </div>
                </div>
                <div class="card-body">
                    @foreach($invoice->items as $item)
                        <div class="doc-b-item">
                            <div>
                                <div class="doc-b-item-title">{{ $item->course->title ?? 'Curso no disponible' }}</div>
                                <div class="doc-b-item-meta">{{ ceil($item->quantity) }} {{ ceil($item->quantity) == 1 ? 'inscripción' : 'inscripciones' }} a {{ $F::money($item->subtotal) }}</div>
                            </div>
                            <div class="doc-b-item-amount doc-num">{{ $F::money($item->total) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card mb-0">
                <div class="card-header border-bottom">
                    <h6 class="mb-1 fw-bold">Distribuidor</h6>
                    <p class="text-muted small mb-0">A quién se le factura.</p>
                </div>
                <div class="card-body">
                    <dl class="doc-b-facts">
                        <div>
                            <dt>Razón social</dt>
                            <dd>{{ $distributor->title ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt>NIT</dt>
                            <dd>{{ $distributor?->nit ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt>Dirección</dt>
                            <dd>{{ $distributor?->address ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt>Correo</dt>
                            <dd>{{ $distributor?->email ?: '—' }}</dd>
                        </div>
                        @if($invoice->notes)
                            <div>
                                <dt>Notas</dt>
                                <dd>{{ $invoice->notes }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

        </div>

        <div class="col-lg-4">
            <div class="doc-b-aside">

                <div class="card">
                    <div class="card-body">
                        <img src="{{ getlogo() }}" alt="{{ config('app.name') }}" class="doc-b-logo">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="doc-b-total-label">{{ $tone === 'paid' ? 'Total pagado' : 'Total por cobrar' }}</div>
                            <span class="badge {{ $tone === 'paid' ? 'bg-success-subtle text-success' : ($tone === 'rejected' ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary') }}">{{ $invoice->condition?->title }}</span>
                        </div>
                        <div class="doc-b-total doc-num">{{ $F::money($invoice->total_invoices_amount) }}</div>
                        <ul class="doc-b-lines">
                            <li><span>Base</span><strong class="doc-num">{{ $F::money($invoice->total_after_discount) }}</strong></li>
                            @if($invoice->total_discount_amount > 0)
                                <li><span>Descuentos</span><strong class="doc-num">− {{ $F::money($invoice->total_discount_amount) }}</strong></li>
                            @endif
                            <li><span>IVA</span><strong class="doc-num">{{ $F::money($invoice->total_tax_amount) }}</strong></li>
                            <li><span>Forma de pago</span><strong>{{ $invoice->method?->title ?? '—' }}</strong></li>
                        </ul>
                    </div>
                </div>

                <div class="card mb-0">
                    <div class="card-header border-bottom">
                        <h6 class="mb-0 fw-bold">Historial</h6>
                    </div>
                    <div class="card-body">
                        <ol class="doc-b-timeline">
                            @if($invoice->from_at)
                                <li class="doc-b-event is-done">
                                    <div class="doc-b-event-title">Periodo facturado</div>
                                    <div class="doc-b-event-date">Del {{ $F::date($invoice->from_at) }} al {{ $F::date($invoice->to_at) }}</div>
                                </li>
                            @endif
                            <li class="doc-b-event is-done">
                                <div class="doc-b-event-title">Factura emitida</div>
                                <div class="doc-b-event-date">{{ $F::date($invoice->created_at) }}</div>
                            </li>
                            @if($tone === 'paid')
                                <li class="doc-b-event is-done">
                                    <div class="doc-b-event-title">Pago recibido</div>
                                    <div class="doc-b-event-date">{{ $invoice->payment_at ? $F::date($invoice->payment_at) : 'Sin fecha registrada' }}</div>
                                </li>
                            @elseif($tone === 'rejected')
                                <li class="doc-b-event is-rejected">
                                    <div class="doc-b-event-title">Factura rechazada</div>
                                    <div class="doc-b-event-date">Última actualización: {{ $F::date($invoice->updated_at) }}</div>
                                </li>
                            @else
                                <li class="doc-b-event is-waiting">
                                    <div class="doc-b-event-title">Esperando pago</div>
                                    <div class="doc-b-event-date">Estado actual: {{ Str::lower($invoice->condition?->title ?? 'sin estado') }}</div>
                                </li>
                            @endif
                        </ol>
                    </div>
                </div>

            </div>
        </div>

    </div>
