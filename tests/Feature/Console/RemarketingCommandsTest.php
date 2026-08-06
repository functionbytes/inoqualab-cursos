<?php

namespace Tests\Feature\Console;

use App\Events\Inscriptions\InscriptionCreated;
use App\Mail\Customers\Courses\AccessExpiringMail;
use App\Mail\Customers\Courses\CompletedCourseMail;
use App\Models\Course\Course;
use App\Models\Inscription;
use App\Models\NewsletterList;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\NewsletterListSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Cobertura de las automatizaciones de remarketing: los comandos encolan el
 * correo a su cohorte de un día, dan de alta en la lista dinámica, registran la
 * corrida, y la compra (InscriptionCreated) retira al alumno de las listas.
 */
class RemarketingCommandsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(NewsletterListSeeder::class);
    }

    private function completedInscription(string $date): Inscription
    {
        return Inscription::factory()
            ->for(User::factory()->create(['email' => 'u'.uniqid().'@example.com']))
            ->for(Course::factory()->create(), 'course')
            ->create([
                'culminated' => 1,
                'enroll_culminated' => $date,
            ]);
    }

    public function test_notify_completed_queues_mail_adds_to_list_and_logs_run(): void
    {
        Mail::fake();
        $target = Carbon::today()->subDay()->toDateString();
        $this->completedInscription($target);
        // Cohorte de OTRO día: no debe recibir.
        $this->completedInscription(Carbon::today()->subDays(10)->toDateString());

        $this->artisan('courses:notify-completed', ['--days' => 1])->assertExitCode(0);

        Mail::assertQueued(CompletedCourseMail::class, 1);
        $this->assertSame(1, NewsletterList::forTrigger('course_completed')->subscribers()->count());
        $this->assertDatabaseHas('remarketing_runs', [
            'command' => 'courses:notify-completed',
            'found' => 1,
            'sent' => 1,
        ]);
    }

    public function test_notify_completed_sends_nothing_for_empty_cohort(): void
    {
        Mail::fake();

        $this->artisan('courses:notify-completed', ['--days' => 1])->assertExitCode(0);

        Mail::assertNothingQueued();
        $this->assertDatabaseHas('remarketing_runs', [
            'command' => 'courses:notify-completed',
            'found' => 0,
            'sent' => 0,
        ]);
    }

    public function test_notify_expiring_access_targets_uncompleted_expiring(): void
    {
        Mail::fake();
        $target = Carbon::today()->addDays(7)->toDateString();

        // Acceso por vencer SIN completar → recibe.
        Inscription::factory()
            ->for(User::factory()->create(['email' => 'exp@example.com']))
            ->for(Course::factory()->create(), 'course')
            ->create(['culminated' => 0, 'enroll_expire' => $target]);

        // Mismo vencimiento pero YA completado → NO recibe.
        Inscription::factory()
            ->for(User::factory()->create(['email' => 'done@example.com']))
            ->for(Course::factory()->create(), 'course')
            ->create(['culminated' => 1, 'enroll_expire' => $target]);

        $this->artisan('courses:notify-expiring-access', ['--days' => 7])->assertExitCode(0);

        Mail::assertQueued(AccessExpiringMail::class, 1);
        $this->assertSame(1, NewsletterList::forTrigger('course_access_expiring')->subscribers()->count());
    }

    public function test_purchase_removes_subscriber_from_dynamic_lists(): void
    {
        // InscriptionCreated dispara también InscriptionListener (envía correo).
        Mail::fake();

        $user = User::factory()->create(['email' => 'buyer@example.com']);
        $list = NewsletterList::forTrigger('course_completed');
        $list->addByEmail($user->email, $user->firstname, 'test');
        $this->assertSame(1, $list->subscribers()->count());

        $inscription = Inscription::factory()
            ->for($user)
            ->for(Course::factory()->create(), 'course')
            ->create();

        // La compra/matrícula dispara el listener de baja (síncrono en tests).
        InscriptionCreated::dispatch($inscription);

        $this->assertSame(0, $list->fresh()->subscribers()->count());
        // El suscriptor sigue existiendo (solo se le retira de la lista dinámica).
        $this->assertDatabaseHas('newsletters', ['email' => 'buyer@example.com']);
    }
}
