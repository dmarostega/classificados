<?php

namespace App\Services\Marketplaces\Adapters;

use App\Services\Marketplaces\AbstractMarketplaceAdapter;
use App\Services\Marketplaces\MarketplaceDraft;
use App\Services\Marketplaces\MarketplaceListingInput;

final class FacebookMarketplaceAdapter extends AbstractMarketplaceAdapter
{
    public function draft(MarketplaceListingInput $listing): MarketplaceDraft
    {
        $title = "{$listing->title} - {$listing->location}";
        $description = $this->description($listing);

        return new MarketplaceDraft(
            marketplace: 'facebook',
            label: 'Facebook Marketplace',
            title: $title,
            description: $description,
            shortText: "{$listing->title} por {$listing->price}. Em {$listing->location}.",
            fullText: $this->fullText($listing, $title, $description),
            suggestedCategory: $listing->category,
            price: $listing->price,
            checklist: $this->manualChecklist('o Facebook Marketplace'),
            warnings: [...$this->manualWarnings(), 'Confira as regras de categoria e anuncio do Facebook antes de publicar.'],
        );
    }
}
