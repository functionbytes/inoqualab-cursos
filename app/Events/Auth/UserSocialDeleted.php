<?php

namespace App\Events\Auth;

use Illuminate\Queue\SerializesModels;

/**
 * Class UserSocialDeleted.
 */
class UserSocialDeleted
{
    use SerializesModels;

    public $user;

    public $social;

    /**
     * UserSocialDeleted constructor.
     */
    public function __construct($user, $social)
    {
        $this->user = $user;
        $this->social = $social;
    }
}
