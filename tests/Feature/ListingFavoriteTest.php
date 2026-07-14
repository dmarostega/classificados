<?php

use App\Enums\ListingStatus;
use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingFavorite;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->category = Category::query()->create([
        'name' => 'Eletronicos',
        'slug' => 'eletronicos',
        'is_active' => true,
        'sort_order' => 10,
    ]);
});

it('allows an authenticated user to favorite a public listing without duplicates', function (): void {
    $user = User::factory()->create();
    $listing = favoriteListing($this->category);

    $this->actingAs($user)->post("/favoritos/{$listing->slug}")->assertRedirect();
    $this->actingAs($user)->post("/favoritos/{$listing->slug}")->assertRedirect();

    $this->assertDatabaseHas('listing_favorites', [
        'user_id' => $user->id,
        'listing_id' => $listing->id,
    ]);
    $this->assertDatabaseCount('listing_favorites', 1);
});

it('does not persist favorites for guests', function (): void {
    $listing = favoriteListing($this->category);

    $this->post("/favoritos/{$listing->slug}")->assertRedirect('/login');

    $this->assertDatabaseCount('listing_favorites', 0);
});

it('blocks favorites for listings that are not publicly visible', function (): void {
    $user = User::factory()->create();
    $draft = favoriteListing($this->category, [
        'title' => 'Notebook em rascunho',
        'slug' => 'notebook-em-rascunho',
        'status' => ListingStatus::Draft,
        'published_at' => null,
    ]);
    $unpublished = favoriteListing($this->category, [
        'title' => 'Notebook nao publicado',
        'slug' => 'notebook-nao-publicado',
        'published_at' => null,
    ]);
    $expired = favoriteListing($this->category, [
        'title' => 'Notebook expirado',
        'slug' => 'notebook-expirado',
        'expires_at' => now()->subMinute(),
    ]);

    foreach ([$draft, $unpublished, $expired] as $listing) {
        $this->actingAs($user)->post("/favoritos/{$listing->slug}")->assertNotFound();
    }

    $this->assertDatabaseCount('listing_favorites', 0);
});

it('removes only the authenticated users favorite', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $listing = favoriteListing($this->category);
    ListingFavorite::query()->create(['user_id' => $user->id, 'listing_id' => $listing->id]);
    ListingFavorite::query()->create(['user_id' => $otherUser->id, 'listing_id' => $listing->id]);

    $this->actingAs($user)->delete("/favoritos/{$listing->slug}")->assertRedirect();

    $this->assertDatabaseMissing('listing_favorites', [
        'user_id' => $user->id,
        'listing_id' => $listing->id,
    ]);
    $this->assertDatabaseHas('listing_favorites', [
        'user_id' => $otherUser->id,
        'listing_id' => $listing->id,
    ]);
});

it('shows the favorite state in the listing detail and catalog', function (): void {
    $user = User::factory()->create();
    $listing = favoriteListing($this->category);
    ListingFavorite::query()->create(['user_id' => $user->id, 'listing_id' => $listing->id]);

    $this->actingAs($user)
        ->get("/anuncios/{$listing->slug}")
        ->assertInertia(fn (Assert $page) => $page->where('listing.is_favorited', true));

    $this->actingAs($user)
        ->get('/anuncios')
        ->assertInertia(fn (Assert $page) => $page->where('listings.data.0.is_favorited', true));
});

it('lists only the authenticated users public favorites', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $publicListing = favoriteListing($this->category);
    $hiddenListing = favoriteListing($this->category, [
        'title' => 'Celular expirado',
        'slug' => 'celular-expirado',
        'expires_at' => now()->subMinute(),
    ]);
    $otherListing = favoriteListing($this->category, [
        'title' => 'Tablet de outro usuario',
        'slug' => 'tablet-de-outro-usuario',
    ]);
    ListingFavorite::query()->create(['user_id' => $user->id, 'listing_id' => $publicListing->id]);
    ListingFavorite::query()->create(['user_id' => $user->id, 'listing_id' => $hiddenListing->id]);
    ListingFavorite::query()->create(['user_id' => $otherUser->id, 'listing_id' => $otherListing->id]);

    $this->actingAs($user)
        ->get('/favoritos')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Favorites/Index')
            ->has('listings.data', 1)
            ->where('listings.data.0.id', $publicListing->id)
            ->where('listings.data.0.is_favorited', true));
});

/**
 * @param  array<string, mixed>  $overrides
 */
function favoriteListing(Category $category, array $overrides = []): Listing
{
    $advertiser = User::factory()->create();

    return Listing::query()->create([
        'user_id' => $advertiser->id,
        'category_id' => $category->id,
        'title' => 'Notebook usado',
        'slug' => 'notebook-usado',
        'description' => 'Notebook conservado e pronto para uso.',
        'price_cents' => 250000,
        'city' => 'Florianopolis',
        'state' => 'SC',
        'contact_name' => 'Anunciante',
        'status' => ListingStatus::Published,
        'published_at' => now(),
        ...$overrides,
    ]);
}
