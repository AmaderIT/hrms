<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;

class LogoutSuccessful
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
     * @param Logout $event
     * @return void
     */
    public function handle(Logout $event)
    {
        $event->subject =  "logout";
        $event->description =  "User Logged Out";

        activity('user-auth')
            ->by($event->user)
            ->log($event->description);
    }
}
