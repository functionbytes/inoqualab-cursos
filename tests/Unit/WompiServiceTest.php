<?php

namespace Tests\Unit;

use App\Services\WompiService;
use Tests\TestCase;

class WompiServiceTest extends TestCase
{
    public function test_integrity_signature_is_deterministic_sha256(): void
    {
        $service = new WompiService;

        $a = $service->generateIntegritySignature('REF123', 100000);
        $b = $service->generateIntegritySignature('REF123', 100000);

        $this->assertSame($a, $b, 'La firma debe ser determinista para los mismos datos.');
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $a, 'Debe ser un SHA-256 en hex.');

        $different = $service->generateIntegritySignature('REF124', 100000);
        $this->assertNotSame($a, $different, 'Referencias distintas deben producir firmas distintas.');
    }

    public function test_checkout_url_contains_required_params(): void
    {
        $service = new WompiService;

        $url = $service->checkoutUrl('REF123', 100000, [
            'email' => 'cliente@example.com',
            'firstname' => 'Ana',
            'lastname' => 'Pérez',
            'cellphone' => '3001234567',
        ], 'https://training.test/payments/response');

        $this->assertStringStartsWith('https://checkout.wompi.co/p/?', $url);
        $this->assertStringContainsString('currency=COP', $url);
        $this->assertStringContainsString('amount-in-cents=100000', $url);
        $this->assertStringContainsString('reference=REF123', $url);
        $this->assertStringContainsString('signature:integrity=', $url);
        $this->assertStringContainsString('redirect-url=', $url);
    }
}
