<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\ListingFavorite;
use App\Models\User;

class ListingFavoriteService
{
    public function add(User $user, Listing $listing): void
    {
        abort_unless($listing->isPubliclyVisible(), 404);

        ListingFavorite::query()->insertOrIgnore([
            'user_id' => $user->id,
            'listing_id' => $listing->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function remove(User $user, Listing $listing): void
    {
        ListingFavorite::query()
            ->whereBelongsTo($user)
            ->whereBelongsTo($listing)
            ->delete();
    }
}
