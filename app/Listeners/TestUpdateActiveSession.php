<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Session;

class UpdateActiveSession
{
    public function handle(Login $event)
    {
        // When user logs in, save their current Session ID to the database
        $event->user->update([
            'active_session_id' => Session::getId()
        ]);
    }
}