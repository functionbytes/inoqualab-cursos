<?php

namespace Tests\Feature\Supports\Distributors;

use App\Models\Course\Course;
use App\Models\Distributor\Distributor;
use App\Models\Distributor\DistributorCourse;
use App\Models\Enterprise\Enterprise;
use App\Models\Order\OrderCondition;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Blinda el ownership del enrolamiento administrativo (soporte): un agente no debe
 * poder matricular a un usuario que no pertenece a la empresa, ni usar una empresa
 * que no pertenece al distribuidor. Cierra el IDOR reportado en la auditoría.
 */
class InscriptionOwnershipTest extends TestCase
{
    use RefreshDatabase;

    private function makeEnterprise(): Enterprise
    {
        return Enterprise::factory()->create();
    }

    private function seedLookups(): void
    {
        OrderType::firstOrCreate(['slug' => 'services'], ['slack' => 'ot', 'title' => 'Servicios', 'slug' => 'services']);
        OrderMethod::firstOrCreate(['slug' => 'credit'], ['slack' => 'om', 'title' => 'Crédito', 'slug' => 'credit']);
        OrderCondition::firstOrCreate(['slug' => 'payment'], ['slack' => 'oc', 'title' => 'Pagada', 'slug' => 'payment']);
    }

    /** @return array{support:User, distributor:Distributor, enterprise:Enterprise, member:User, course:Course} */
    private function scenario(): array
    {
        $this->seedLookups();

        $support = User::factory()->create(['role' => 'support', 'available' => 1, 'validation' => 1]);
        $distributor = Distributor::factory()->create();
        $enterprise = $this->makeEnterprise();
        $distributor->enterprises()->attach($enterprise->id);

        $member = User::factory()->create(['role' => 'customer', 'available' => 1, 'validation' => 1]);
        $enterprise->users()->attach($member->id);

        $course = Course::factory()->create();
        DistributorCourse::create([
            'distributor_id' => $distributor->id,
            'course_id' => $course->id,
            'price' => 50000,
        ]);

        return compact('support', 'distributor', 'enterprise', 'member', 'course');
    }

    public function test_support_can_enroll_user_belonging_to_the_enterprise(): void
    {
        Mail::fake();
        ['support' => $support, 'distributor' => $d, 'enterprise' => $e, 'member' => $u, 'course' => $c] = $this->scenario();

        $this->actingAs($support)
            ->postJson(route('support.distributors.inscriptions.enroll'), [
                'enterprise' => $e->id,
                'course' => $c->id,
                'user' => $u->id,
                'distributor' => $d->id,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('inscriptions', ['user_id' => $u->id, 'course_id' => $c->id]);
    }

    public function test_support_cannot_enroll_user_not_belonging_to_the_enterprise(): void
    {
        Mail::fake();
        ['support' => $support, 'distributor' => $d, 'enterprise' => $e, 'course' => $c] = $this->scenario();

        // Usuario ajeno a la empresa (IDOR): no está en enterprise_user.
        $outsider = User::factory()->create(['role' => 'customer', 'available' => 1, 'validation' => 1]);

        $this->actingAs($support)
            ->postJson(route('support.distributors.inscriptions.enroll'), [
                'enterprise' => $e->id,
                'course' => $c->id,
                'user' => $outsider->id,
                'distributor' => $d->id,
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('inscriptions', ['user_id' => $outsider->id, 'course_id' => $c->id]);
    }

    public function test_support_cannot_enroll_using_enterprise_not_owned_by_distributor(): void
    {
        Mail::fake();
        ['support' => $support, 'distributor' => $d, 'member' => $u, 'course' => $c] = $this->scenario();

        // Empresa que NO pertenece al distribuidor.
        $foreignEnterprise = $this->makeEnterprise();
        $foreignEnterprise->users()->attach($u->id);

        $this->actingAs($support)
            ->postJson(route('support.distributors.inscriptions.enroll'), [
                'enterprise' => $foreignEnterprise->id,
                'course' => $c->id,
                'user' => $u->id,
                'distributor' => $d->id,
            ])
            ->assertNotFound();
    }
}
