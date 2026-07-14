<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\User;

class SitemapService
{
    /** @return list<string> */
    public function urls(): array
    {
        $listings = Listing::query()
            ->public()
            ->with('user:id,slug')
            ->orderBy('id')
            ->get(['id', 'user_id', 'slug']);

        $listingUrls = $listings
            ->map(fn (Listing $listing): string => route('listings.show', $listing->slug));

        $advertiserUrls = $listings
            ->pluck('user')
            ->filter()
            ->unique('id')
            ->map(fn (User $advertiser): string => route('advertisers.show', $advertiser->slug));

        return collect([
            route('home'),
            route('listings.index'),
        ])
            ->concat($listingUrls)
            ->concat($advertiserUrls)
            ->values()
            ->all();
    }
}
