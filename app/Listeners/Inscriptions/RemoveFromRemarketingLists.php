<?php

namespace App\Listeners\Inscriptions;

use App\Events\Inscriptions\InscriptionCreated;
use App\Models\Newsletter;
use App\Models\NewsletterList;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Baja al convertir: cuando un alumno se matricula en un curso (compra), se le
 * retira de las listas dinámicas de remarketing — ya convirtió, no hay que
 * seguir empujándole a comprar. Las listas manuales no se tocan.
 */
class RemoveFromRemarketingLists implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public int $backoff = 5;

    public function handle(InscriptionCreated $event): void
    {
        $email = $event->inscription->user->email ?? null;
        if (! $email) {
            return;
        }

        $subscriber = Newsletter::where('email', $email)->first();
        if (! $subscriber) {
            return;
        }

        $dynamicListIds = NewsletterList::dynamic()->pluck('id')->all();
        if ($dynamicListIds) {
            $subscriber->lists()->detach($dynamicListIds);
        }
    }

    public function failed(InscriptionCreated $event, \Throwable $exception): void
    {
        Log::error('Listener failed: '.static::class, ['error' => $exception->getMessage()]);
    }
}
