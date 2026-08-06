<?php

namespace Tests\Unit\Models;

use App\Models\NewsletterList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Regresión: NewsletterList::forTrigger() (antes un scope Eloquent
 * `scopeTrigger` que terminaba en ->first()). Builder::callScope() hace
 * `return $scope(...) ?? $this`: cuando no hay ninguna lista activa para el
 * trigger, ->first() devuelve null, pero un scope Eloquent con ese null lo
 * reemplaza en silencio por el propio Builder. NewsletterList::trigger('x')
 * devolvía entonces un Builder, no null -- y $list?->addByEmail(...) en los
 * 3 comandos de remarketing (certificates:notify-expiring,
 * courses:notify-completed, courses:notify-expiring-access) explotaba con
 * BadMethodCallException porque el operador null-safe no detecta un Builder,
 * abortando el comando ENTERO sin ningún try/catch alrededor.
 */
class NewsletterListForTriggerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_null_when_no_list_exists_for_the_trigger(): void
    {
        $this->assertNull(NewsletterList::forTrigger('trigger_sin_lista'));
    }

    public function test_returns_null_when_the_matching_list_is_inactive(): void
    {
        NewsletterList::create([
            'slack' => (string) Str::uuid(),
            'name' => 'Inactiva',
            'trigger' => 'course_completed',
            'is_active' => false,
        ]);

        $this->assertNull(NewsletterList::forTrigger('course_completed'));
    }

    public function test_returns_the_active_list_for_the_trigger(): void
    {
        $list = NewsletterList::create([
            'slack' => (string) Str::uuid(),
            'name' => 'Cross-sell post-completación',
            'trigger' => 'course_completed',
            'is_active' => true,
        ]);

        $found = NewsletterList::forTrigger('course_completed');

        $this->assertNotNull($found);
        $this->assertSame($list->id, $found->id);
    }
}
