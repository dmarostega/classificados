<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(LocationSeeder::class);

        collect([
            ['name' => 'Veiculos', 'slug' => 'veiculos', 'sort_order' => 10],
            ['name' => 'Motos', 'slug' => 'motos', 'sort_order' => 20],
            ['name' => 'Pecas e Acessorios', 'slug' => 'pecas-e-acessorios', 'sort_order' => 30],
            ['name' => 'Imoveis', 'slug' => 'imoveis', 'sort_order' => 40],
            ['name' => 'Moveis', 'slug' => 'moveis', 'sort_order' => 50],
            ['name' => 'Casa e Jardim', 'slug' => 'casa-e-jardim', 'sort_order' => 60],
            ['name' => 'Eletronicos', 'slug' => 'eletronicos', 'sort_order' => 70],
            ['name' => 'Celulares e Telefonia', 'slug' => 'celulares-e-telefonia', 'sort_order' => 80],
            ['name' => 'Informatica', 'slug' => 'informatica', 'sort_order' => 90],
            ['name' => 'Moda e Beleza', 'slug' => 'moda-e-beleza', 'sort_order' => 100],
            ['name' => 'Bebes e Criancas', 'slug' => 'bebes-e-criancas', 'sort_order' => 110],
            ['name' => 'Esportes e Lazer', 'slug' => 'esportes-e-lazer', 'sort_order' => 120],
            ['name' => 'Animais', 'slug' => 'animais', 'sort_order' => 130],
            ['name' => 'Servicos', 'slug' => 'servicos', 'sort_order' => 140],
            ['name' => 'Empregos', 'slug' => 'empregos', 'sort_order' => 150],
            ['name' => 'Cursos e Aulas', 'slug' => 'cursos-e-aulas', 'sort_order' => 160],
            ['name' => 'Eventos', 'slug' => 'eventos', 'sort_order' => 170],
            ['name' => 'Agro e Industria', 'slug' => 'agro-e-industria', 'sort_order' => 180],
            ['name' => 'Ferramentas e Maquinas', 'slug' => 'ferramentas-e-maquinas', 'sort_order' => 190],
            ['name' => 'Musica e Hobbies', 'slug' => 'musica-e-hobbies', 'sort_order' => 200],
            ['name' => 'Outros', 'slug' => 'outros', 'sort_order' => 900],
        ])->each(fn (array $category): Category => Category::query()->updateOrCreate(
            ['slug' => $category['slug']],
            $category + ['is_active' => true],
        ));

        if (app()->isLocal() && ! User::query()->where('email', 'dev@rockcode.com.br')->exists()) {
            User::factory()->create(['name' => 'Rock Code', 'email' => 'dev@rockcode.com.br']);
        }
    }
}
