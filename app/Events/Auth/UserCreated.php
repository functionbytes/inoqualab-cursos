<?php

namespace App\Events\Auth;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class UserCreated.
 */
class UserCreated
{
    use Dispatchable, SerializesModels;

    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }
}
