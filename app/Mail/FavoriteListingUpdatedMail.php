<?php

namespace App\Mail;

use App\Models\ListingFavorite;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class FavoriteListingUpdatedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly ListingFavorite $favorite) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Um anuncio favorito foi atualizado');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.favorite-listing-updated',
            with: [
                'unsubscribeUrl' => URL::signedRoute(
                    'favorite-notifications.unsubscribe',
                    ['favorite' => $this->favorite->id],
                ),
            ],
        );
    }
}
