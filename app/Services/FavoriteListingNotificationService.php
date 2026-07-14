<?php

namespace App\Services;

use App\Jobs\SendFavoriteListingUpdatedMail;
use App\Models\Listing;
use App\Models\ListingFavorite;
use Illuminate\Database\Eloquent\Collection;

class FavoriteListingNotificationService
{
    public function dispatchFor(Listing $listing): void
    {
        $listing->favorites()
            ->where('email_notifications_enabled', true)
            ->where('user_id', '!=', $listing->user_id)
            ->select('id')
            ->chunkById(100, function (Collection $favorites): void {
                $favorites->each(
                    fn (ListingFavorite $favorite) => SendFavoriteListingUpdatedMail::dispatch($favorite->id)
                        ->afterCommit()
                );
            });
    }
}
