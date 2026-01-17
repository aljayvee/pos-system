<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LogIntegrityMail extends Mailable
{
    use Queueable, SerializesModels;

    public $status;
    public $report;

    /**
     * Create a new message instance.
     * 
     * @param array $report Full report from verifyChain
     */
    public function __construct(array $report)
    {
        $this->report = $report;
        $this->status = $report['status'] ?? 'UNKNOWN';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->status === 'OK'
            ? '✅ System Integrity: SECURE [' . now()->format('Y-m-d') . ']'
            : '🚨 CRITICAL ALERT: Log Integrity Breach Detected!';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.admin.integrity_report',
            with: [
                'report' => $this->report,
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
