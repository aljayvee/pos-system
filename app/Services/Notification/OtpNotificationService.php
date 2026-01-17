<?php

namespace App\Services\Notification;

use App\Models\User;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;

class OtpNotificationService
{
    /**
     * Send OTP via Email.
     *
     * @param User $user The recipient
     * @param string $code The 6-digit code
     * @param string $actionName Readable action (e.g., "Reset Password")
     */
    public function sendViaEmail(User $user, string $code, string $actionName)
    {
        $this->sendToEmail($user->email, $code, $actionName, $user->name);
    }

    public function sendToEmail(string $email, string $code, string $actionName, string $name = 'User')
    {
        Mail::to($email)->send(new OtpMail($code, $actionName, $name));
    }
}
