<?php

namespace App\Services\Marketplaces;

final readonly class MarketplaceDraft
{
    /**
     * @param  list<string>  $checklist
     * @param  list<string>  $warnings
     */
    public function __construct(
        public string $marketplace,
        public string $label,
        public string $title,
        public string $description,
        public string $shortText,
        public string $fullText,
        public ?string $suggestedCategory,
        public string $price,
        public array $checklist,
        public array $warnings,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'marketplace' => $this->marketplace,
            'label' => $this->label,
            'title' => $this->title,
            'description' => $this->description,
            'short_text' => $this->shortText,
            'full_text' => $this->fullText,
            'suggested_category' => $this->suggestedCategory,
            'price' => $this->price,
            'checklist' => $this->checklist,
            'warnings' => $this->warnings,
        ];
    }
}
