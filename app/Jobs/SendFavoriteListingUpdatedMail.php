<?php

namespace App\Jobs;

use App\Mail\FavoriteListingUpdatedMail;
use App\Models\ListingFavorite;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendFavoriteListingUpdatedMail implements ShouldQueue
{
    use Queueable;

    private const COOLDOWN_MINUTES = 10;

    public function __construct(public readonly int $favoriteId) {}

    public function handle(): void
    {
        Cache::lock("favorite-listing-notification:{$this->favoriteId}", 60)
            ->get(function (): void {
                $favorite = ListingFavorite::query()
                    ->with(['listing', 'user'])
                    ->find($this->favoriteId);

                if (! $this->canSend($favorite)) {
                    return;
                }

                Mail::to($favorite->user)->send(new FavoriteListingUpdatedMail($favorite));

                $favorite->forceFill(['last_notification_sent_at' => now()])->save();

                Log::info('Favorite listing update notification sent.', [
                    'favorite_id' => $favorite->id,
                    'listing_id' => $favorite->listing_id,
                    'user_id' => $favorite->user_id,
                ]);
            });
    }

    private function canSend(?ListingFavorite $favorite): bool
    {
        if (! $favorite || ! $favorite->email_notifications_enabled) {
            return false;
        }

        if ($favorite->user_id === $favorite->listing->user_id || ! $favorite->listing->isPubliclyVisible()) {
            return false;
        }

        return $favorite->last_notification_sent_at === null
            || $favorite->last_notification_sent_at->lte(now()->subMinutes(self::COOLDOWN_MINUTES));
    }
}
