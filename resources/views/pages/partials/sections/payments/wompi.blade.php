<form>
    <script
        type="text/javascript"
        src="https://checkout.wompi.co/widget.js"
        data-render="button"
        data-public-key="{{ $wompi->public }}"
        data-currency="{{ $wompi->currency }}"
        data-amount-in-cents="{{ $wompi->amount }}"
        data-reference="{{ $wompi->reference }}"
        data-expiration-time="{{ $wompi->expiration }}"
        data-redirect-url="{{ $wompi->redirect }}"
        data-signature:integrity="{{ $wompi->signature }}"
        data-customer-data:email="{{ $wompi->email }}"
        data-customer-data:full-name="{{ $wompi->firstname }} {{ $wompi->lastname }}"
        data-customer-data:phone-number="{{ $wompi->cellphone }}"
        data-customer-data:phone-number-prefix="+57"
    ></script>
    <button class="waybox-button d-none" type="submit">Paga con <strong>Wompi</strong></button>
</form>

@if(setting('wompi_sandbox') === 'true')
<div class="mt-3 p-3" style="background:#fff8e1;border:2px dashed #f9a825;border-radius:8px;">
    <p class="mb-2" style="font-size:12px;"><strong>🧪 Sandbox:</strong> Simula el resultado del pago sin usar Wompi real.</p>
    <div class="d-flex" style="gap:8px;flex-wrap:wrap;">
        <a href="{{ route('checkout.simulate', [$wompi->reference, 'APPROVED']) }}"
           class="btn btn-sm"
           style="background:#2e7d32;color:#fff;font-size:12px;">
            ✓ Simular APROBADO
        </a>
        <a href="{{ route('checkout.simulate', [$wompi->reference, 'DECLINED']) }}"
           class="btn btn-sm"
           style="background:#c62828;color:#fff;font-size:12px;">
            ✗ Simular DECLINADO
        </a>
        <a href="{{ route('checkout.simulate', [$wompi->reference, 'PENDING']) }}"
           class="btn btn-sm"
           style="background:#1565c0;color:#fff;font-size:12px;">
            ⏳ Simular PENDIENTE
        </a>
        <a href="{{ route('checkout.simulate', [$wompi->reference, 'ERROR']) }}"
           class="btn btn-sm"
           style="background:#455a64;color:#fff;font-size:12px;">
            ⚠ Simular ERROR
        </a>
    </div>
</div>
@endif
