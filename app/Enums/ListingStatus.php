<?php

namespace App\Enums;

enum ListingStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Paused = 'paused';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Rascunho',
            self::Published => 'Publicado',
            self::Paused => 'Pausado',
            self::Archived => 'Arquivado',
        };
    }

    public function isPublic(): bool
    {
        return $this === self::Published;
    }
}
