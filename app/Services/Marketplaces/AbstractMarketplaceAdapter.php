<?php

namespace App\Services\Marketplaces;

abstract class AbstractMarketplaceAdapter implements MarketplaceAdapterInterface
{
    protected function description(MarketplaceListingInput $listing): string
    {
        return trim(implode("\n\n", array_filter([
            $listing->description,
            "Preco: {$listing->price}",
            "Localizacao: {$listing->location}",
            $listing->publicUrl ? "Mais detalhes e fotos: {$listing->publicUrl}" : null,
        ])));
    }

    protected function fullText(MarketplaceListingInput $listing, string $title, string $description): string
    {
        return "{$title}\n\n{$description}";
    }

    /** @return list<string> */
    protected function manualChecklist(string $channel): array
    {
        return [
            "Abrir {$channel} e iniciar uma nova publicacao manualmente.",
            'Copiar o titulo, a descricao e o preco sugeridos.',
            'Selecionar a categoria e conferir cidade, fotos e condicoes antes de publicar.',
            'Revisar o texto final e publicar diretamente no canal escolhido.',
        ];
    }

    /** @return list<string> */
    protected function manualWarnings(): array
    {
        return [
            'A publicacao final deve ser feita manualmente no marketplace escolhido.',
            'Este assistente nao solicita nem armazena credenciais de plataformas externas.',
            'Revise o texto e remova dados pessoais antes de compartilhar o anuncio.',
        ];
    }
}
