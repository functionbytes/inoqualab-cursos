<?php

namespace App\Events\Inscriptions;

use App\Models\Inscription;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InscriptionCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $inscription;

    public function __construct(Inscription $inscription)
    {
        $this->inscription = $inscription;
    }
}
