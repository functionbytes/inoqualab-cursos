<?php

namespace App\Events\Auth;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class UserDeleted.
 */
class UserDeleted
{
    use Dispatchable, SerializesModels;

    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }
}
