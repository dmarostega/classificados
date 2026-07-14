<?php

use App\Enums\ListingStatus;
use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Database\Seeders\LocationSeeder;

beforeEach(function (): void {
    $this->seed(LocationSeeder::class);
});

function categoryForAdvertiserPage(): Category
{
    return Category::query()->create([
        'name' => 'Moveis',
        'slug' => 'moveis',
        'is_active' => true,
        'sort_order' => 10,
    ]);
}

it('shows only public active listings from the advertiser', function (): void {
    $advertiser = User::factory()->create([
        'name' => 'Loja Central',
        'email' => 'privado@example.com',
    ]);
    $otherAdvertiser = User::factory()->create();
    $category = categoryForAdvertiserPage();

    Listing::query()->create([
        'user_id' => $advertiser->id,
        'category_id' => $category->id,
        'title' => 'Sofa publicado',
        'slug' => 'sofa-publicado',
        'description' => 'Sofa publicado pelo anunciante principal.',
        'price_cents' => 90000,
        'city' => 'Jaragua do Sul',
        'state' => 'SC',
        'contact_name' => 'Loja Central',
        'status' => ListingStatus::Published,
        'published_at' => now(),
    ]);
    Listing::query()->create([
        'user_id' => $advertiser->id,
        'category_id' => $category->id,
        'title' => 'Mesa rascunho',
        'slug' => 'mesa-rascunho',
        'description' => 'Anuncio em rascunho nao deve aparecer.',
        'price_cents' => 30000,
        'city' => 'Jaragua do Sul',
        'state' => 'SC',
        'contact_name' => 'Loja Central',
        'status' => ListingStatus::Draft,
    ]);
    Listing::query()->create([
        'user_id' => $advertiser->id,
        'category_id' => $category->id,
        'title' => 'Cadeira expirada',
        'slug' => 'cadeira-expirada',
        'description' => 'Anuncio expirado nao deve aparecer.',
        'price_cents' => 12000,
        'city' => 'Jaragua do Sul',
        'state' => 'SC',
        'contact_name' => 'Loja Central',
        'status' => ListingStatus::Published,
        'published_at' => now()->subDays(10),
        'expires_at' => now()->subDay(),
    ]);
    Listing::query()->create([
        'user_id' => $otherAdvertiser->id,
        'category_id' => $category->id,
        'title' => 'Produto de outro anunciante',
        'slug' => 'produto-outro-anunciante',
        'description' => 'Anuncio publicado por outro anunciante.',
        'price_cents' => 45000,
        'city' => 'Jaragua do Sul',
        'state' => 'SC',
        'contact_name' => 'Outro anunciante',
        'status' => ListingStatus::Published,
        'published_at' => now(),
    ]);

    $this->get(route('advertisers.show', $advertiser->slug))->assertOk()
        ->assertSee('Public\\/Advertisers\\/Show', false)
        ->assertSee('Loja Central')
        ->assertSee('<title inertia>Anuncios de Loja Central | Classificados</title>', false)
        ->assertSee('<meta name="description" content="Veja anuncios publicados por Loja Central no Classificados.">', false)
        ->assertSee('<link rel="canonical" href="http://localhost/anunciantes/'.$advertiser->slug.'">', false)
        ->assertSee('<meta name="robots" content="index,follow">', false)
        ->assertSee('Sofa publicado')
        ->assertDontSee('Mesa rascunho')
        ->assertDontSee('Cadeira expirada')
        ->assertDontSee('Produto de outro anunciante')
        ->assertDontSee('privado@example.com');
});

it('returns not found when advertiser has no public listings', function (): void {
    $advertiser = User::factory()->create();

    $this->get(route('advertisers.show', $advertiser->slug))->assertNotFound();
});

it('generates unique advertiser slugs and redirects the legacy url', function (): void {
    $advertiser = User::factory()->create(['name' => 'Loja Central']);
    $advertiserWithSameName = User::factory()->create(['name' => 'Loja Central']);

    expect($advertiser->slug)->toBe('loja-central')
        ->and($advertiserWithSameName->slug)->toBe('loja-central-2');

    $this->get(route('advertisers.show', $advertiser->id))
        ->assertRedirect(route('advertisers.show', $advertiser->slug))
        ->assertStatus(301);
});

it('exposes the advertiser page link in public listing detail payload', function (): void {
    $advertiser = User::factory()->create(['name' => 'Vendedor Local']);
    $category = categoryForAdvertiserPage();
    $listing = Listing::query()->create([
        'user_id' => $advertiser->id,
        'category_id' => $category->id,
        'title' => 'Armario publicado',
        'slug' => 'armario-publicado',
        'description' => 'Armario publicado pelo vendedor local.',
        'price_cents' => 50000,
        'city' => 'Jaragua do Sul',
        'state' => 'SC',
        'contact_name' => 'Vendedor Local',
        'contact_phone' => '(47) 99999-1234',
        'status' => ListingStatus::Published,
        'published_at' => now(),
    ]);

    $this->get(route('listings.show', $listing))->assertOk()
        ->assertSee('Vendedor Local')
        ->assertSee('anunciantes\\/'.$advertiser->slug, false)
        ->assertSee('(47) 9****-1234')
        ->assertDontSee('(47) 99999-1234')
        ->assertDontSee('tel:');
});
