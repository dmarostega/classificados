<?php

namespace App\Services\Marketplaces\Adapters;

use App\Services\Marketplaces\AbstractMarketplaceAdapter;
use App\Services\Marketplaces\MarketplaceDraft;
use App\Services\Marketplaces\MarketplaceListingInput;

final class WhatsAppAdapter extends AbstractMarketplaceAdapter
{
    public function draft(MarketplaceListingInput $listing): MarketplaceDraft
    {
        $title = $listing->title;
        $description = trim(implode("\n", array_filter([
            "Estou vendendo: {$listing->title}",
            $listing->description,
            "Preco: {$listing->price}",
            "Cidade: {$listing->location}",
            $listing->publicUrl ? "Detalhes e fotos: {$listing->publicUrl}" : null,
        ])));

        return new MarketplaceDraft(
            marketplace: 'whatsapp',
            label: 'WhatsApp',
            title: $title,
            description: $description,
            shortText: "Vendo {$listing->title} por {$listing->price}, em {$listing->location}.",
            fullText: $description,
            suggestedCategory: null,
            price: $listing->price,
            checklist: $this->manualChecklist('uma conversa ou grupo do WhatsApp'),
            warnings: [...$this->manualWarnings(), 'Evite compartilhar dados pessoais alem dos necessarios para a negociacao.'],
        );
    }
}
