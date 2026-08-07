<?php

namespace App\Events\Auth;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class UserDeactivated.
 */
class UserDeactivated
{
    use Dispatchable, SerializesModels;

    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }
}
