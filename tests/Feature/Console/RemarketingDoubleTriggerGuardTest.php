<?php

namespace Tests\Feature\Console;

use App\Models\Course\Course;
use App\Models\Inscription;
use App\Models\RemarketingRun;
use App\Models\User;
use App\Models\Users\Certificate;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Regresión: certificates:notify-expiring, courses:notify-completed y
 * courses:notify-expiring-access no tenían ninguna guarda contra doble
 * disparo (reintento manual, cron duplicado). RemarketingRun ya registraba
 * cada corrida (command + cohort_date) pero nada la consultaba antes de
 * reenviar -- una segunda corrida para el mismo día de cohorte reenviaba a
 * toda la cohorte, mismo patrón del bug de newsletter ya arreglado antes.
 */
class RemarketingDoubleTriggerGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_certificates_notify_expiring_does_not_resend_on_second_run_same_cohort(): void
    {
        Mail::fake();

        $user = User::factory()->create(['role' => 'customer']);
        $course = Course::factory()->create();
        $target = Carbon::today()->addDays(30);

        Certificate::create([
            'slack' => 'cert-'.uniqid(),
            'user_id' => $user->id,
            'course_id' => $course->id,
            'start_at' => $target->copy()->subYear(),
            'end_at' => $target,
        ]);

        $this->artisan('certificates:notify-expiring --days=30')->assertSuccessful();
        $this->assertSame(1, RemarketingRun::where('command', 'certificates:notify-expiring')->count());

        $this->artisan('certificates:notify-expiring --days=30')
            ->expectsOutputToContain('Ya se ejecutó')
            ->assertSuccessful();

        // Sin la guarda esto quedaría en 2 (una fila de RemarketingRun por corrida).
        $this->assertSame(1, RemarketingRun::where('command', 'certificates:notify-expiring')->count());
    }

    public function test_courses_notify_completed_does_not_resend_on_second_run_same_cohort(): void
    {
        Mail::fake();

        $user = User::factory()->create(['role' => 'customer']);
        $course = Course::factory()->create();
        $target = Carbon::today()->subDay();

        Inscription::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'culminated' => 1,
            'enroll_culminated' => $target,
        ]);

        $this->artisan('courses:notify-completed --days=1')->assertSuccessful();
        $this->assertSame(1, RemarketingRun::where('command', 'courses:notify-completed')->count());

        $this->artisan('courses:notify-completed --days=1')
            ->expectsOutputToContain('Ya se ejecutó')
            ->assertSuccessful();

        $this->assertSame(1, RemarketingRun::where('command', 'courses:notify-completed')->count());
    }

    public function test_courses_notify_expiring_access_does_not_resend_on_second_run_same_cohort(): void
    {
        Mail::fake();

        $user = User::factory()->create(['role' => 'customer']);
        $course = Course::factory()->create();
        $target = Carbon::today()->addDays(7);

        Inscription::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'culminated' => 0,
            'enroll_expire' => $target,
        ]);

        $this->artisan('courses:notify-expiring-access --days=7')->assertSuccessful();
        $this->assertSame(1, RemarketingRun::where('command', 'courses:notify-expiring-access')->count());

        $this->artisan('courses:notify-expiring-access --days=7')
            ->expectsOutputToContain('Ya se ejecutó')
            ->assertSuccessful();

        $this->assertSame(1, RemarketingRun::where('command', 'courses:notify-expiring-access')->count());
    }
}
