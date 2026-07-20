<?php

use App\Enums\ListingStatus;
use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use App\Services\Marketplaces\MarketplaceDraftService;
use App\Services\Marketplaces\MarketplaceListingInput;
use Inertia\Testing\AssertableInertia as Assert;

function marketplaceListing(User $user): Listing
{
    $category = Category::query()->create([
        'name' => 'Eletronicos',
        'slug' => 'eletronicos',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    return Listing::query()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Notebook Dell Inspiron',
        'slug' => 'notebook-dell-inspiron',
        'description' => 'Notebook conservado, com carregador original e pronto para uso.',
        'price_cents' => 250000,
        'city' => 'Maringa',
        'state' => 'PR',
        'contact_name' => 'Anunciante',
        'status' => ListingStatus::Published,
        'published_at' => now(),
    ]);
}

it('generates manual drafts for every supported marketplace', function (): void {
    $listing = marketplaceListing(User::factory()->create());
    $listing->load('category');

    $drafts = app(MarketplaceDraftService::class)->draftsFor($listing);

    expect($drafts)->toHaveCount(4)
        ->and(array_column($drafts, 'marketplace'))->toBe(['facebook', 'olx', 'whatsapp', 'generic'])
        ->and($drafts[2]['full_text'])->toContain('R$ 2.500,00')
        ->and($drafts[0]['warnings'][0])->toContain('manualmente')
        ->and($drafts[1]['suggested_category'])->toBe('Eletronicos');
});

it('shows marketplace drafts only to the listing owner', function (): void {
    $owner = User::factory()->create();
    $listing = marketplaceListing($owner);

    $this->actingAs($owner)->get("/admin/anuncios/{$listing->id}/publicar")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Listings/Publish')
            ->has('drafts', 4)
            ->where('drafts.0.marketplace', 'facebook')
            ->where('listing.public_url', route('listings.show', $listing->slug)));

    $this->actingAs(User::factory()->create())
        ->get("/admin/anuncios/{$listing->id}/publicar")
        ->assertForbidden();
});

it('does not include a public link for listings that are not publicly visible', function (): void {
    $listing = marketplaceListing(User::factory()->create());
    $listing->update([
        'status' => ListingStatus::Draft,
        'published_at' => null,
    ]);
    $listing->load('category');

    $input = MarketplaceListingInput::fromListing($listing);
    $drafts = app(MarketplaceDraftService::class)->draftsFor($listing);

    expect($input->publicUrl)->toBeNull()
        ->and(implode("\n", array_column($drafts, 'full_text')))->not->toContain('/anuncios/');
});
