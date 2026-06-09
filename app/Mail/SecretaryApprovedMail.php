<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SecretaryApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $secretary,
        public User $medecin,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: '✅ Votre demande d\'accès a été approuvée - MediCabinet',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.secretary-approved',
            with: [
                'secretary' => $this->secretary,
                'medecin' => $this->medecin,
                'loginUrl' => config('app.frontend_url') . '/login',
            ],
        );
    }
}
