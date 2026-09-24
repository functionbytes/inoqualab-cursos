{{-- details · diseño C — contenido compartido por todos los perfiles.
     Página: managers.views.documents.page. Enlaces en $links (details/view). --}}
@php
    $F = \App\Html\DocumentFormat::class;
    $tone = $F::tone($invoice->condition_id);
    $groups = $F::enterpriseGroups($details);
    $detailTotal = $groups->sum('total');
    $seats = $groups->sum('seats');
    $share = fn ($value) => $detailTotal > 0 ? round($value / $detailTotal * 100, 1) : 0;
    $top = $groups->take(5);
    $restTotal = $groups->slice(5)->sum('total');
@endphp
    <div class="doc-c">

        <div class="doc-c-band">
            <div class="doc-c-band-id">
                <img src="{{ getlogo() }}" alt="{{ config('app.name') }}" class="doc-c-logo">
                <span class="doc-c-band-rule" aria-hidden="true"></span>
                <div>
                <div class="doc-c-band-kind">Reparto por empresa</div>
                <div class="doc-c-band-code">{{ $invoice->reference }}</div>
                </div>
            </div>
            <span class="doc-c-state is-{{ $tone }}">{{ $invoice->condition?->title ?? 'Sin estado' }}</span>
        </div>

        <div class="doc-c-grid">
            <div class="doc-c-cell is-wide">
                <div class="doc-c-cell-label">Distribuidor</div>
                <div class="doc-c-cell-value">{{ $invoice->distributor->title ?? '—' }}</div>
            </div>
            <div class="doc-c-cell">
                <div class="doc-c-cell-label">Empresas</div>
                <div class="doc-c-cell-value doc-num">{{ $groups->count() }}</div>
            </div>
            <div class="doc-c-cell">
                <div class="doc-c-cell-label">Inscripciones</div>
                <div class="doc-c-cell-value doc-num">{{ number_format($seats, 0, ',', '.') }}</div>
            </div>
            <div class="doc-c-cell">
                <div class="doc-c-cell-label">Periodo desde</div>
                <div class="doc-c-cell-value">{{ $invoice->from_at ? $F::date($invoice->from_at, 'd/m/Y') : '—' }}</div>
            </div>
            <div class="doc-c-cell">
                <div class="doc-c-cell-label">Periodo hasta</div>
                <div class="doc-c-cell-value">{{ $invoice->to_at ? $F::date($invoice->to_at, 'd/m/Y') : '—' }}</div>
            </div>
            <div class="doc-c-cell">
                <div class="doc-c-cell-label">Suma del reparto</div>
                <div class="doc-c-cell-value doc-num">{{ $F::money($detailTotal) }}</div>
            </div>
            <div class="doc-c-cell">
                <div class="doc-c-cell-label">Total factura</div>
                <div class="doc-c-cell-value doc-num">{{ $F::money($invoice->total_invoices_amount) }}</div>
            </div>
        </div>

        @if($groups->isNotEmpty())
            <div class="doc-c-split">
                <div class="doc-c-split-bar" role="img" aria-label="Reparto del valor entre {{ $groups->count() }} empresas">
                    @foreach($top as $group)
                        <span class="doc-c-seg-{{ $loop->iteration }}" data-width="{{ $share($group['total']) }}" title="{{ $group['name'] }}: {{ $F::percent($share($group['total'])) }}"></span>
                    @endforeach
                    @if($restTotal > 0)
                        <span class="doc-c-seg-rest" data-width="{{ $share($restTotal) }}" title="Otras {{ $groups->count() - 5 }} empresas: {{ $F::percent($share($restTotal)) }}"></span>
                    @endif
                </div>
                <ul class="doc-c-legend">
                    @foreach($top as $group)
                        <li><i class="doc-c-seg-{{ $loop->iteration }}"></i>{{ Str::limit($group['name'], 28) }} <strong class="doc-num">{{ $F::percent($share($group['total'])) }}</strong></li>
                    @endforeach
                    @if($restTotal > 0)
                        <li><i class="doc-c-seg-rest"></i>Otras {{ $groups->count() - 5 }} empresas <strong class="doc-num">{{ $F::percent($share($restTotal)) }}</strong></li>
                    @endif
                </ul>
            </div>

            <div class="doc-c-table-wrap">
                <table class="doc-c-table">
                    <thead>
                        <tr>
                            <th class="doc-c-line">N.º</th>
                            <th>Empresa y curso</th>
                            <th class="is-center">Inscripciones</th>
                            <th class="is-end">Valor</th>
                            <th class="is-end">Participación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($groups as $group)
                            <tr class="is-group">
                                <td class="doc-c-line doc-num">{{ $loop->iteration }}</td>
                                <td>{{ $group['name'] }}</td>
                                <td class="is-center doc-num">{{ number_format($group['seats'], 0, ',', '.') }}</td>
                                <td class="is-end doc-num">{{ $F::money($group['total']) }}</td>
                                <td class="is-end doc-num">{{ $F::percent($share($group['total'])) }}</td>
                            </tr>
                            @foreach($group['courses'] as $course)
                                <tr class="is-sub">
                                    <td></td>
                                    <td>{{ $course['course'] }}</td>
                                    <td class="is-center doc-num">{{ ceil($course['quantity']) }}</td>
                                    <td class="is-end doc-num">{{ $F::money($course['totalAmount']) }}</td>
                                    <td></td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted text-center py-5 mb-0">
                Esta factura no tiene inscripciones asociadas a empresas, así que no hay reparto que mostrar.
            </p>
        @endif

    </div>
