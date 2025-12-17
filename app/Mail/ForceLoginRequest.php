<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class ForceLoginRequest extends Mailable
{
    use Queueable, SerializesModels;

    public $url;
    public $deviceDetails;

    public function __construct($user, $deviceDetails)
    {
        // Generate a signed URL that expires in 15 minutes
        $this->url = URL::temporarySignedRoute(
            'auth.force_login_verify',
            now()->addMinutes(15),
            ['id' => $user->id]
        );
        $this->deviceDetails = $deviceDetails;
    }

    public function build()
    {
        return $this->subject('Alert: Login Attempt Blocked - Action Required')
                    ->view('emails.force-login');
    }
}