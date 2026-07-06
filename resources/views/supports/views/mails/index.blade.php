@extends('layouts.managers')

@section('content')

    @include('supports.includes.card', ['title' => 'Correos entrantes'])

    <div class="widget-content searchable-container list">

        {{-- Filtros por estado --}}
        <div class="card card-body">
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('support.mails.index') }}"
                   class="btn btn-sm {{ is_null($status) ? 'btn-primary' : 'btn-outline-secondary' }}">
                    Todos
                    <span class="badge bg-white text-dark ms-1">{{ $counts->sum() }}</span>
                </a>
                <a href="{{ route('support.mails.index', ['status' => 'pending_review']) }}"
                   class="btn btn-sm {{ $status === 'pending_review' ? 'btn-warning' : 'btn-outline-warning' }}">
                    Pendientes
                    @if($counts->has('pending_review'))
                        <span class="badge bg-white text-dark ms-1">{{ $counts->get('pending_review') }}</span>
                    @endif
                </a>
                <a href="{{ route('support.mails.index', ['status' => 'processed']) }}"
                   class="btn btn-sm {{ $status === 'processed' ? 'btn-success' : 'btn-outline-success' }}">
                    Procesados
                    @if($counts->has('processed'))
                        <span class="badge bg-white text-dark ms-1">{{ $counts->get('processed') }}</span>
                    @endif
                </a>
                <a href="{{ route('support.mails.index', ['status' => 'failed']) }}"
                   class="btn btn-sm {{ $status === 'failed' ? 'btn-danger' : 'btn-outline-danger' }}">
                    Fallidos
                    @if($counts->has('failed'))
                        <span class="badge bg-white text-dark ms-1">{{ $counts->get('failed') }}</span>
                    @endif
                </a>
                <a href="{{ route('support.mails.index', ['status' => 'ignored']) }}"
                   class="btn btn-sm {{ $status === 'ignored' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                    Ignorados
                    @if($counts->has('ignored'))
                        <span class="badge bg-white text-dark ms-1">{{ $counts->get('ignored') }}</span>
                    @endif
                </a>
            </div>
        </div>

        {{-- Buscador --}}
        <div class="card card-body">
            <div class="row">
                <div class="col-md-12 col-xl-12">
                    <form class="position-relative form-search" action="{{ route('support.mails.index') }}" method="GET">
                        @if($status)
                            <input type="hidden" name="status" value="{{ $status }}">
                        @endif
                        <div class="row justify-content-between g-2">
                            <div class="col-auto flex-grow-1">
                                <div class="tt-search-box">
                                    <div class="input-group">
                                        <input class="form-control rounded-start w-100 ps-5" type="text" id="search" name="search" placeholder="Buscar por remitente o asunto" value="{{ request('search') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Buscar">
                                    <i class="fa-duotone fa-magnifying-glass"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="card card-body">
            <div class="table-responsive">
                <table class="table search-table align-middle text-nowrap">
                    <thead class="header-item">
                        <tr>
                            <th scope="col">Remitente</th>
                            <th scope="col">Asunto</th>
                            <th scope="col">Empresa</th>
                            <th scope="col">Confianza</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Fecha</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mails as $mail)
                            <tr class="search-items">
                                <td>
                                    <span class="usr-email-addr">{{ $mail->from }}</span>
                                </td>
                                <td>
                                    <span class="usr-email-addr">{{ Str::limit($mail->subject, 60) }}</span>
                                </td>
                                <td>
                                    @if($mail->enterprise)
                                        <span>{{ $mail->enterprise->title }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!is_null($mail->confidence_score))
                                        @php
                                            $score = $mail->confidence_score;
                                            $badgeClass = $score >= 90 ? 'bg-success' : ($score >= 50 ? 'bg-warning' : 'bg-danger');
                                        @endphp
                                        <span class="badge {{ $badgeClass }} rounded-3 py-2 fw-semibold fs-2">{{ $score }}%</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $badgeMap = [
                                            'pending_review' => ['class' => 'bg-light-warning text-warning', 'label' => 'Pendiente'],
                                            'processed'      => ['class' => 'bg-light-success text-success', 'label' => 'Procesado'],
                                            'failed'         => ['class' => 'bg-light-danger text-danger',   'label' => 'Fallido'],
                                            'ignored'        => ['class' => 'bg-light-secondary text-secondary', 'label' => 'Ignorado'],
                                        ];
                                        $badge = $badgeMap[$mail->status] ?? ['class' => 'bg-light-secondary text-secondary', 'label' => $mail->status];
                                    @endphp
                                    <span class="badge {{ $badge['class'] }} rounded-3 py-2 fw-semibold fs-2 d-inline-flex align-items-center gap-1">
                                        {{ $badge['label'] }}
                                    </span>
                                </td>
                                <td>
                                    <span class="usr-ph-no">{{ \Carbon\Carbon::parse($mail->received_at)->format('Y-m-d H:i') }}</span>
                                </td>
                                <td class="text-left">
                                    <div class="dropdown dropstart">
                                        <a href="#" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fa-duotone fa-ellipsis-vertical fs-5"></i>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('support.mails.show', $mail->slack) }}">Revisar</a>
                                            </li>
                                            @if($mail->status === 'processed' && $mail->order)
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('support.users.orders.view', $mail->order->slack) }}">Ver orden</a>
                                                </li>
                                            @endif
                                            @if($mail->status !== 'ignored')
                                                <li>
                                                    <a class="dropdown-item discard-btn" href="#" data-slack="{{ $mail->slack }}">Descartar</a>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="result-body">
                @if($mails->total() > 0)
                    <span>Mostrar {{ $mails->firstItem() }}-{{ $mails->lastItem() }} de {{ $mails->total() }} resultados</span>
                @else
                    <span>Sin resultados</span>
                @endif
                <nav>
                    {{ $mails->appends(request()->input())->links() }}
                </nav>
            </div>
        </div>
    </div>

    {{-- Modal descartar --}}
    <div id="discard-modal" class="modal fade">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Descartar correo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="display-4 text-warning"><i class="fa-duotone fa-triangle-exclamation"></i></div>
                    <h4 class="my-0">¿Descartar este correo?</h4>
                    <p>El correo será marcado como ignorado y no generará una orden.</p>
                    <div class="row justify-content-center mt-3">
                        <div class="col-sm-12 col-md-6">
                            <button type="button" id="discard-confirm-btn" class="btn btn-danger w-100 mb-2">Confirmar</button>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script type="text/javascript">
    $(document).ready(function () {

        var currentSlack = null;

        $(document).on('click', '.discard-btn', function (e) {
            e.preventDefault();
            currentSlack = $(this).data('slack');
            $('#discard-modal').modal('show');
        });

        $('#discard-confirm-btn').on('click', function () {
            if (!currentSlack) return;

            var url = '{{ route("support.mails.discard", ":slack") }}'.replace(':slack', currentSlack);

            $.ajax({
                url: url,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (response) {
                    $('#discard-modal').modal('hide');
                    if (response.success) {
                        toastr.success(response.message, 'Listo', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
                        setTimeout(function () { location.reload(); }, 1000);
                    } else {
                        toastr.error(response.message, 'Error', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
                    }
                },
                error: function () {
                    $('#discard-modal').modal('hide');
                    toastr.error('Error al procesar la solicitud.', 'Error', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
                }
            });
        });

    });
</script>
@endpush
