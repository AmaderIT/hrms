<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class LoginSuccessful
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param Login $event
     * @return void
     */
    public function handle(Login $event)
    {
        $event->subject =  "login";
        $event->description =  "User Logged In";

        activity('user-auth')
            ->by($event->user)
            ->log($event->description);
    }
}
