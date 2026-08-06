<?php

namespace Tests\Unit\Listeners;

use App\Events\Inscriptions\InscriptionCreated;
use App\Listeners\Inscriptions\InscriptionListener;
use App\Mail\Distributors\Inscriptions\InscriptionsMails;
use App\Models\Course\Course;
use App\Models\Inscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Regresión: handleMailInscription() no tenía guardia de email (a diferencia
 * de handleMailReport(), en el mismo listener, que sí hace `if (! $email)
 * return;`). Los usuarios creados desde correos entrantes
 * (OrderCreator::resolveUser()) nunca tienen email -- ningún parser lo
 * extrae del correo. Sin el guard, el constructor de InscriptionsMails tira
 * TypeError (su propiedad $email es string, no nullable) al asignarle null,
 * y el job falla en silencio en la cola 'emails' (ShouldQueue).
 */
class InscriptionListenerTest extends TestCase
{
    use RefreshDatabase;

    public function test_handle_mail_inscription_does_not_crash_when_the_user_has_no_email(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email' => null, 'role' => 'customer']);
        $course = Course::factory()->create();
        $inscription = Inscription::factory()->for($user)->for($course, 'course')->create();

        (new InscriptionListener)->handleMailInscription(new InscriptionCreated($inscription));

        Mail::assertNothingQueued();
    }

    public function test_handle_mail_inscription_queues_the_mail_when_the_user_has_an_email(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email' => 'alumno@example.test', 'role' => 'customer']);
        $course = Course::factory()->create();
        $inscription = Inscription::factory()->for($user)->for($course, 'course')->create();

        (new InscriptionListener)->handleMailInscription(new InscriptionCreated($inscription));

        Mail::assertQueued(InscriptionsMails::class);
    }
}
