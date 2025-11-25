<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class SecurityAlertMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public $user;
    public $alertType;
    public $details;

    public function __construct(
        User $user, 
        string $alertType = 'suspicious_activity', 
        array $details = []
    ) {
        $this->user = $user;
        $this->alertType = $alertType;
        $this->details = $details;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Security Alert for Your Account',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.security-alert',
            with: [
                'user' => $this->user,
                'alertType' => $this->alertType,
                'details' => $this->details
            ]
        );
    }
}