<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            ['name' => 'Veiculos', 'slug' => 'veiculos', 'sort_order' => 10],
            ['name' => 'Imoveis', 'slug' => 'imoveis', 'sort_order' => 20],
            ['name' => 'Eletronicos', 'slug' => 'eletronicos', 'sort_order' => 30],
            ['name' => 'Casa e Jardim', 'slug' => 'casa-e-jardim', 'sort_order' => 40],
            ['name' => 'Servicos', 'slug' => 'servicos', 'sort_order' => 50],
            ['name' => 'Outros', 'slug' => 'outros', 'sort_order' => 90],
        ])->each(fn (array $category): Category => Category::query()->updateOrCreate(
            ['slug' => $category['slug']],
            $category + ['is_active' => true]
        ));

        if (app()->isLocal()) {
            User::factory()->create(['name' => 'Rock Code', 'email' => 'dev@rockcode.com.br']);
        }
    }
}
