<?php

namespace App\Services\Marketplaces\Adapters;

use App\Services\Marketplaces\AbstractMarketplaceAdapter;
use App\Services\Marketplaces\MarketplaceDraft;
use App\Services\Marketplaces\MarketplaceListingInput;

final class GenericMarketplaceAdapter extends AbstractMarketplaceAdapter
{
    public function draft(MarketplaceListingInput $listing): MarketplaceDraft
    {
        $title = $listing->title;
        $description = $this->description($listing);

        return new MarketplaceDraft(
            marketplace: 'generic',
            label: 'Grupos e redes sociais',
            title: $title,
            description: $description,
            shortText: "{$listing->title} - {$listing->price} - {$listing->location}",
            fullText: $this->fullText($listing, $title, $description),
            suggestedCategory: $listing->category,
            price: $listing->price,
            checklist: $this->manualChecklist('o grupo ou rede social'),
            warnings: $this->manualWarnings(),
        );
    }
}
