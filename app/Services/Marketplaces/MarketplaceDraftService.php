<?php

namespace App\Services\Marketplaces;

use App\Models\Listing;
use App\Services\Marketplaces\Adapters\FacebookMarketplaceAdapter;
use App\Services\Marketplaces\Adapters\GenericMarketplaceAdapter;
use App\Services\Marketplaces\Adapters\OlxAdapter;
use App\Services\Marketplaces\Adapters\WhatsAppAdapter;

final readonly class MarketplaceDraftService
{
    /** @return list<array<string, mixed>> */
    public function draftsFor(Listing $listing): array
    {
        $input = MarketplaceListingInput::fromListing($listing);

        return array_map(
            fn (MarketplaceAdapterInterface $adapter): array => $adapter->draft($input)->toArray(),
            [
                new FacebookMarketplaceAdapter,
                new OlxAdapter,
                new WhatsAppAdapter,
                new GenericMarketplaceAdapter,
            ],
        );
    }
}
