<?php

namespace App\Services\Marketplaces;

interface MarketplaceAdapterInterface
{
    public function draft(MarketplaceListingInput $listing): MarketplaceDraft;
}
