<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SecretaryRefusedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $secretary,
        public User $medecin,
        public ?string $reason = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: '❌ Décision concernant votre demande d\'accès - MediCabinet',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.secretary-refused',
            with: [
                'secretary' => $this->secretary,
                'medecin' => $this->medecin,
                'reason' => $this->reason,
                'contactUrl' => config('app.frontend_url') . '/contact',
            ],
        );
    }
}
