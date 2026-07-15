<?php

use App\Enums\ListingStatus;
use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use App\Support\Seo\SeoData;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the home page', function (): void {
    $this->withoutVite();

    $this->get('/')->assertOk();
});

it('normalizes public and private page metadata', function (): void {
    config(['seo.title_suffix' => ' | Teste']);

    expect(SeoData::page('Pagina', '<strong>Descricao</strong>')->toArray())
        ->toMatchArray(['title' => 'Pagina | Teste', 'description' => 'Descricao'])
        ->and(SeoData::privatePage('Painel')->toArray())
        ->toMatchArray(['title' => 'Painel | Teste', 'robots' => 'noindex,nofollow']);
});

it('lists only public indexable urls in the sitemap', function (): void {
    expect(file_get_contents(resource_path('views/sitemap.blade.php')))
        ->not->toContain('<?xml');

    $category = Category::query()->create([
        'name' => 'Eletronicos',
        'slug' => 'eletronicos',
        'is_active' => true,
        'sort_order' => 10,
    ]);
    $publicAdvertiser = User::factory()->create(['name' => 'Loja Publica']);
    $hiddenAdvertiser = User::factory()->create(['name' => 'Loja Oculta']);
    $publicListing = seoListing($category, $publicAdvertiser, 'anuncio-publico');
    $draftListing = seoListing($category, $publicAdvertiser, 'anuncio-rascunho', [
        'status' => ListingStatus::Draft,
        'published_at' => null,
    ]);
    $unpublishedListing = seoListing($category, $hiddenAdvertiser, 'anuncio-nao-publicado', [
        'published_at' => null,
    ]);
    $expiredListing = seoListing($category, $hiddenAdvertiser, 'anuncio-expirado', [
        'expires_at' => now()->subMinute(),
    ]);

    $response = $this->get('/sitemap.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml');
    $xml = simplexml_load_string($response->getContent());

    expect($xml)->not->toBeFalse();

    $xml->registerXPathNamespace('sitemap', 'http://www.sitemaps.org/schemas/sitemap/0.9');
    $locations = array_map(
        static fn (SimpleXMLElement $location): string => (string) $location,
        $xml->xpath('//sitemap:loc') ?: [],
    );

    expect($locations)->toBe([
        route('home'),
        route('listings.index'),
        route('listings.show', $publicListing->slug),
        route('advertisers.show', $publicAdvertiser->slug),
    ])->not->toContain(
        route('listings.show', $draftListing->slug),
        route('listings.show', $unpublishedListing->slug),
        route('listings.show', $expiredListing->slug),
        route('advertisers.show', $hiddenAdvertiser->slug),
        route('login'),
        route('dashboard'),
        route('favorites.index'),
        route('admin.listings.index'),
    );
});

it('marks authenticated pages as noindex and nofollow', function (): void {
    $user = User::factory()->create();
    $category = Category::query()->create([
        'name' => 'Moveis',
        'slug' => 'moveis',
        'is_active' => true,
        'sort_order' => 10,
    ]);
    $listing = seoListing($category, $user, 'mesa-do-anunciante');

    foreach ([
        route('dashboard'),
        route('favorites.index'),
        route('admin.listings.index'),
        route('admin.listings.create'),
        route('admin.listings.edit', $listing),
    ] as $url) {
        $this->actingAs($user)
            ->get($url)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('seo.robots', 'noindex,nofollow'));
    }
});

/**
 * @param  array<string, mixed>  $overrides
 */
function seoListing(Category $category, User $advertiser, string $slug, array $overrides = []): Listing
{
    return Listing::query()->create([
        'user_id' => $advertiser->id,
        'category_id' => $category->id,
        'title' => str_replace('-', ' ', $slug),
        'slug' => $slug,
        'description' => 'Anuncio usado para validar o SEO tecnico.',
        'price_cents' => 10000,
        'city' => 'Florianopolis',
        'state' => 'SC',
        'contact_name' => 'Anunciante',
        'status' => ListingStatus::Published,
        'published_at' => now(),
        ...$overrides,
    ]);
}
