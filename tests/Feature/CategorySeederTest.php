<?php

use App\Models\Category;
use Database\Seeders\DatabaseSeeder;

it('seeds a broad classified category catalog', function (): void {
    $this->seed(DatabaseSeeder::class);

    expect(Category::query()->count())->toBeGreaterThanOrEqual(20)
        ->and(Category::query()->where('slug', 'veiculos')->exists())
        ->toBeTrue()
        ->and(Category::query()->where('slug', 'imoveis')->exists())
        ->toBeTrue()
        ->and(Category::query()->where('slug', 'servicos')->exists())
        ->toBeTrue()
        ->and(Category::query()->where('slug', 'outros')->exists())
        ->toBeTrue();
});
