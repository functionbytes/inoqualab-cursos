@forelse ($mails as $mail)
    @php
        $ageHours = \Carbon\Carbon::parse($mail->received_at)->diffInHours(now());
        $ageClass = in_array($mail->status, ['pending_review', 'failed'])
            ? ($ageHours < 1 ? 'text-success fw-semibold' : ($ageHours < 24 ? 'text-warning fw-semibold' : 'text-danger fw-semibold'))
            : 'text-muted';
        $ageTitle = \Carbon\Carbon::parse($mail->received_at)->diffForHumans();
    @endphp
    <tr class="search-items">
        <td class="px-2">
            <input type="checkbox" class="form-check-input mail-checkbox" value="{{ $mail->slack }}">
        </td>
        <td>
            <a href="{{ route('manager.mails.show', $mail->slack) }}" class="fw-semibold text-dark">
                {{ $mail->from }}
            </a>
        </td>
        <td>{{ Str::limit($mail->subject, 55) }}</td>
        <td>
            @if($mail->enterprise)
                {{ $mail->enterprise->title }}
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td>
            @if(!is_null($mail->confidence_score))
                @php
                    $score = $mail->confidence_score;
                    $bc = $score >= 90 ? 'bg-success' : ($score >= 50 ? 'bg-warning' : 'bg-danger');
                @endphp
                <span class="badge {{ $bc }} rounded-3 py-1 px-2">{{ $score }}%</span>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td>
            @php
                $sm = [
                    'pending_review' => ['bg-light-warning text-warning',     'Pendiente'],
                    'processed'      => ['bg-light-success text-success',     'Procesado'],
                    'failed'         => ['bg-light-danger text-danger',       'Fallido'],
                    'ignored'        => ['bg-light-secondary text-secondary', 'Ignorado'],
                ];
                [$sc, $sl] = $sm[$mail->status] ?? ['bg-light-secondary text-secondary', $mail->status];
            @endphp
            <span class="badge {{ $sc }} rounded-3 py-1 px-2 fw-semibold">{{ $sl }}</span>
        </td>
        <td>
            <span class="{{ $ageClass }} small" title="{{ $ageTitle }}">
                {{ \Carbon\Carbon::parse($mail->received_at)->format('Y-m-d H:i') }}
            </span>
        </td>
        <td>
            <div class="d-flex align-items-center gap-2">
                @if($mail->status === 'pending_review' && $mail->confidence_score >= 90 && $mail->enterprise)
                    <button type="button"
                            class="btn btn-success btn-sm quick-confirm-btn"
                            data-slack="{{ $mail->slack }}"
                            data-enterprise="{{ $mail->enterprise->title }}"
                            title="Confirmación rápida">
                        <i class="fas fa-check"></i>
                    </button>
                @endif
                <div class="dropdown dropstart">
                    <a href="#" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-vertical fs-5"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2"
                               href="{{ route('manager.mails.show', $mail->slack) }}">
                                Revisar
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 preview-btn"
                               href="#"
                               data-slack="{{ $mail->slack }}"
                               data-bs-toggle="modal" data-bs-target="#preview-modal">
                                Previsualizar
                            </a>
                        </li>
                        @if($mail->assigned_to !== auth()->id())
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 assign-me-btn"
                                   href="#" data-slack="{{ $mail->slack }}">
                                    Asignarme
                                </a>
                            </li>
                        @endif
                        @if($mail->status === 'processed' && $mail->order)
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2"
                                   href="{{ route('manager.orders.view', $mail->order->slack) }}">
                                    Ver orden
                                </a>
                            </li>
                        @endif
                        @if($mail->status !== 'ignored')
                            <li><div class="dropdown-divider my-1"></div></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 discard-btn"
                                   href="#" data-slack="{{ $mail->slack }}">
                                    Descartar
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center py-4 text-muted">
            Sin resultados
        </td>
    </tr>
@endforelse
