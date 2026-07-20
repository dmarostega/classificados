<?php

namespace App\Services\Marketplaces\Adapters;

use App\Services\Marketplaces\AbstractMarketplaceAdapter;
use App\Services\Marketplaces\MarketplaceDraft;
use App\Services\Marketplaces\MarketplaceListingInput;

final class OlxAdapter extends AbstractMarketplaceAdapter
{
    public function draft(MarketplaceListingInput $listing): MarketplaceDraft
    {
        $title = $listing->title;
        $description = $this->description($listing);

        return new MarketplaceDraft(
            marketplace: 'olx',
            label: 'OLX',
            title: $title,
            description: $description,
            shortText: "{$listing->title}. {$listing->price}. {$listing->location}.",
            fullText: $this->fullText($listing, $title, $description),
            suggestedCategory: $listing->category,
            price: $listing->price,
            checklist: $this->manualChecklist('a OLX'),
            warnings: [...$this->manualWarnings(), 'Escolha a categoria mais proxima da sugestao para melhorar a descoberta do anuncio.'],
        );
    }
}
