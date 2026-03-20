<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactFormSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $messageBody,
    ) {
    }

    public function envelope(): Envelope
    {
        $fullName = trim($this->firstName . ' ' . $this->lastName);

        return new Envelope(
            subject: 'New Contact Form Submission from ' . $fullName,
            replyTo: [
                new \Illuminate\Mail\Mailables\Address($this->email, $fullName),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-form-submitted',
        );
    }
}
