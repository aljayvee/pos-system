<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $code;
    public $actionName;
    public $userName;

    public function __construct($code, $actionName, $userName)
    {
        $this->code = $code;
        $this->actionName = $actionName;
        $this->userName = $userName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->actionName . ' - Verification Code',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.auth.otp',
            with: [
                'code' => $this->code,
                'action' => $this->actionName,
                'name' => $this->userName,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
