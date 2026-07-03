<?php

use App\Enums\ListingStatus;
use App\Mail\ListingContactMail;
use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\ListingImageService;
use Database\Seeders\LocationSeeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    $this->seed(LocationSeeder::class);
});

function categoryForListings(): Category
{
    return Category::query()->create([
        'name' => 'Veiculos',
        'slug' => 'veiculos',
        'is_active' => true,
        'sort_order' => 10,
    ]);
}

it('creates a user scoped listing from the admin panel', function (): void {
    $user = User::factory()->create();
    $category = categoryForListings();

    $this->actingAs($user)->post('/admin/anuncios', [
        'category_id' => $category->id,
        'title' => 'Honda Civic completo',
        'description' => 'Carro em otimo estado, revisado e pronto para transferencia.',
        'price' => '79900.00',
        'city' => 'Maringa',
        'state' => 'pr',
        'contact_name' => 'Dono do anuncio',
        'contact_email' => 'vendedor@example.com',
        'contact_phone' => '(44) 99999-9999',
        'status' => ListingStatus::Published->value,
    ])->assertRedirect();

    $listing = Listing::query()->sole();

    expect($listing->user_id)->toBe($user->id)
        ->and($listing->category_id)->toBe($category->id)
        ->and($listing->price_cents)->toBe(7990000)
        ->and($listing->city)->toBe('Maringa')
        ->and($listing->state)->toBe('PR')
        ->and($listing->published_at)->not->toBeNull();
});

it('rejects listings with cities that do not belong to the selected state', function (): void {
    $user = User::factory()->create();
    $category = categoryForListings();

    $this->actingAs($user)->post('/admin/anuncios', [
        'category_id' => $category->id,
        'title' => 'Produto local',
        'description' => 'Produto anunciado com localizacao invalida para teste.',
        'price' => '120.00',
        'city' => 'Sao Paulo',
        'state' => 'SC',
        'contact_name' => 'Anunciante',
        'status' => ListingStatus::Draft->value,
    ])->assertSessionHasErrors('city');
});

it('rejects listings with unknown states', function (): void {
    $user = User::factory()->create();
    $category = categoryForListings();

    $this->actingAs($user)->post('/admin/anuncios', [
        'category_id' => $category->id,
        'title' => 'Produto local',
        'description' => 'Produto anunciado com estado invalido para teste.',
        'price' => '120.00',
        'city' => 'Maringa',
        'state' => 'XX',
        'contact_name' => 'Anunciante',
        'status' => ListingStatus::Draft->value,
    ])->assertSessionHasErrors('state');
});

it('prevents users from editing another user listing', function (): void {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $category = categoryForListings();
    $listing = Listing::query()->create([
        'user_id' => $owner->id,
        'category_id' => $category->id,
        'title' => 'Notebook Dell',
        'slug' => 'notebook-dell',
        'description' => 'Notebook conservado com carregador original.',
        'price_cents' => 250000,
        'city' => 'Curitiba',
        'state' => 'PR',
        'contact_name' => 'Anunciante',
        'status' => ListingStatus::Draft,
    ]);

    $this->actingAs($otherUser)->get("/admin/anuncios/{$listing->id}/edit")->assertForbidden();
});

it('emails the advertiser when a public listing receives contact', function (): void {
    Mail::fake();

    $user = User::factory()->create(['email' => 'owner@example.com']);
    $category = categoryForListings();
    $listing = Listing::query()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Mesa de escritorio',
        'slug' => 'mesa-de-escritorio',
        'description' => 'Mesa grande em madeira com pouco uso.',
        'price_cents' => 45000,
        'city' => 'Londrina',
        'state' => 'PR',
        'contact_name' => 'Anunciante',
        'contact_email' => 'seller@example.com',
        'status' => ListingStatus::Published,
        'published_at' => now(),
    ]);

    $this->post("/anuncios/{$listing->id}/contato", [
        'name' => 'Comprador',
        'email' => 'buyer@example.com',
        'phone' => '(43) 98888-0000',
        'message' => 'Tenho interesse no produto anunciado.',
    ])->assertRedirect();

    Mail::assertSent(ListingContactMail::class, fn (ListingContactMail $mail): bool => $mail->hasTo('seller@example.com'));
});

it('filters public listings by exact city name', function (): void {
    $user = User::factory()->create();
    $category = categoryForListings();

    Listing::query()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Anuncio Rio Branco',
        'slug' => 'anuncio-rio-branco',
        'description' => 'Anuncio publicado em Rio Branco para validar filtro exato.',
        'price_cents' => 10000,
        'city' => 'Rio Branco',
        'state' => 'AC',
        'contact_name' => 'Anunciante',
        'status' => ListingStatus::Published,
        'published_at' => now(),
    ]);
    Listing::query()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Anuncio Rio de Janeiro',
        'slug' => 'anuncio-rio-de-janeiro',
        'description' => 'Anuncio publicado no Rio de Janeiro para validar filtro exato.',
        'price_cents' => 20000,
        'city' => 'Rio de Janeiro',
        'state' => 'RJ',
        'contact_name' => 'Anunciante',
        'status' => ListingStatus::Published,
        'published_at' => now(),
    ]);

    $this->get('/anuncios?city=Rio')->assertOk()
        ->assertDontSee('Anuncio Rio Branco')
        ->assertDontSee('Anuncio Rio de Janeiro');
});

it('removes listing image links and media assets when updating a listing', function (): void {
    Storage::fake('public');

    $user = User::factory()->create();
    $category = categoryForListings();
    $listing = Listing::query()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Renault Clio',
        'slug' => 'renault-clio',
        'description' => 'Carro conservado para venda local.',
        'price_cents' => 1800000,
        'city' => 'Jaragua do Sul',
        'state' => 'SC',
        'contact_name' => 'Anunciante',
        'status' => ListingStatus::Draft,
    ]);
    Storage::disk('public')->put('media/test.webp', 'image');
    $asset = MediaAsset::query()->create([
        'user_id' => $user->id,
        'disk' => 'public',
        'path' => 'media/test.webp',
        'original_name' => 'test.webp',
        'mime_type' => 'image/webp',
        'size' => 5,
        'kind' => 'image',
    ]);
    $image = ListingImage::query()->create([
        'listing_id' => $listing->id,
        'media_asset_id' => $asset->id,
        'sort_order' => 1,
        'is_cover' => true,
    ]);

    app(ListingImageService::class)->deleteImages($listing, [$image->id]);

    expect(ListingImage::query()->whereKey($image->id)->exists())->toBeFalse()
        ->and(MediaAsset::query()->whereKey($asset->id)->exists())->toBeFalse();
    Storage::disk('public')->assertMissing('media/test.webp');
});

it('ignores listing images without media assets when serializing', function (): void {
    $listing = new Listing;
    $listing->setRelation('images', collect([
        new ListingImage(['id' => 1, 'is_cover' => true]),
    ]));

    $images = app(ListingImageService::class)->serializeImages($listing);

    expect($images)->toHaveCount(0);
});
