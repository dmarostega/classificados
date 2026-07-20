<?php

namespace App\Services\Marketplaces;

use App\Models\Listing;

final readonly class MarketplaceListingInput
{
    public function __construct(
        public string $title,
        public string $description,
        public string $price,
        public ?string $category,
        public string $location,
        public ?string $publicUrl,
    ) {}

    public static function fromListing(Listing $listing): self
    {
        return new self(
            title: $listing->title,
            description: $listing->description,
            price: $listing->formattedPrice(),
            category: $listing->category?->name,
            location: "{$listing->city} / {$listing->state}",
            publicUrl: $listing->isPubliclyVisible() ? $listing->publicUrl() : null,
        );
    }
}
