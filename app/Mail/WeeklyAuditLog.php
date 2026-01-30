<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WeeklyAuditLog extends Mailable
{
    use Queueable, SerializesModels;

    protected $pdfPath;
    protected $password;

    /**
     * Create a new message instance.
     */
    public function __construct($pdfPath, $password)
    {
        $this->pdfPath = $pdfPath;
        $this->password = $password;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Weekly Audit Log Report (Secure)',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.weekly_audit',
            with: [
                'password_hint' => 'Birthdate(YYYY-MM-DD) + MPIN + Username'
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            \Illuminate\Mail\Mailables\Attachment::fromPath($this->pdfPath)
                ->as('security_audit_log.pdf')
                ->withMime('application/pdf')
        ];
    }
}
