<?php

use App\Enums\ListingStatus;
use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->category = Category::query()->create([
        'name' => 'Moveis',
        'slug' => 'moveis',
        'is_active' => true,
        'sort_order' => 10,
    ]);
});

it('keeps the complete phone out of the initial public listing payload', function (): void {
    $listing = publicListingWithPhone($this->category);

    $this->get("/anuncios/{$listing->slug}")
        ->assertOk()
        ->assertDontSee('(47) 99999-1234')
        ->assertInertia(fn (Assert $page) => $page
            ->where('listing.contact_phone_masked', '(47) 9****-1234')
            ->missing('listing.contact_phone'));
});

it('reveals and audits the phone of a publicly visible listing', function (): void {
    $listing = publicListingWithPhone($this->category, [
        'expires_at' => now()->addDay(),
    ]);

    $this->withHeader('User-Agent', 'Feature test')
        ->getJson("/anuncios/{$listing->slug}/telefone")
        ->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertExactJson([
            'phone' => '(47) 99999-1234',
            'phone_href' => 'tel:+5547999991234',
            'whatsapp_url' => 'https://wa.me/5547999991234',
        ]);

    $this->assertDatabaseCount('listing_phone_reveals', 1);
    $this->assertDatabaseHas('listing_phone_reveals', ['listing_id' => $listing->id]);
});

it('does not reveal or audit phones for non-public listings', function (): void {
    $draft = publicListingWithPhone($this->category, [
        'title' => 'Anuncio em rascunho',
        'slug' => 'anuncio-em-rascunho',
        'status' => ListingStatus::Draft,
        'published_at' => null,
    ]);
    $unpublished = publicListingWithPhone($this->category, [
        'title' => 'Anuncio nao publicado',
        'slug' => 'anuncio-nao-publicado',
        'published_at' => null,
    ]);
    $expired = publicListingWithPhone($this->category, [
        'title' => 'Anuncio expirado',
        'slug' => 'anuncio-expirado',
        'published_at' => now()->subDays(2),
        'expires_at' => now()->subDay(),
    ]);

    foreach ([$draft, $unpublished, $expired] as $listing) {
        $this->getJson("/anuncios/{$listing->slug}/telefone")->assertNotFound();
    }

    $this->getJson('/anuncios/inexistente/telefone')->assertNotFound();
    $this->assertDatabaseCount('listing_phone_reveals', 0);
});

/**
 * @param  array<string, mixed>  $overrides
 */
function publicListingWithPhone(Category $category, array $overrides = []): Listing
{
    $user = User::factory()->create();

    return Listing::query()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Bicicleta urbana',
        'slug' => 'bicicleta-urbana',
        'description' => 'Bicicleta revisada e pronta para uso.',
        'price_cents' => 95000,
        'city' => 'Joinville',
        'state' => 'SC',
        'contact_name' => 'Anunciante',
        'contact_phone' => '(47) 99999-1234',
        'status' => ListingStatus::Published,
        'published_at' => now(),
        ...$overrides,
    ]);
}
