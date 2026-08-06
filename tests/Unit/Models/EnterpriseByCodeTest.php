<?php

namespace Tests\Unit\Models;

use App\Models\Enterprise\Enterprise;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: Enterprise::byCode() reemplaza el antiguo scope Eloquent
 * `scopeCode` (terminaba en ->first()). Un scope así hace que
 * Builder::callScope() devuelva el propio Builder (no null) cuando no hay
 * match, por su fallback `$scope(...) ?? $this`. El único caller
 * (EnterpriseMatcher) ya se defendía con `instanceof Enterprise`, pero el
 * método en sí seguía siendo una trampa para cualquier caller futuro.
 */
class EnterpriseByCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_null_when_no_enterprise_matches_the_code(): void
    {
        $this->assertNull(Enterprise::byCode('CODIGO-INEXISTENTE'));
    }

    public function test_returns_the_matching_enterprise(): void
    {
        $enterprise = Enterprise::factory()->create(['code' => 'ACME-001']);

        $found = Enterprise::byCode('ACME-001');

        $this->assertNotNull($found);
        $this->assertSame($enterprise->id, $found->id);
    }
}
