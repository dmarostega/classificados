<?php

namespace App\Mail;

use App\Models\Listing;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ListingContactMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Listing $listing,
        public readonly array $contact
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [$this->contact['email']],
            subject: 'Novo contato para '.$this->listing->title,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.listing-contact');
    }
}
