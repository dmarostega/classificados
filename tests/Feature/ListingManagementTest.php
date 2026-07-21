<?php

use App\Enums\ListingStatus;
use App\Mail\ListingContactMail;
use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\ListingImageService;
use App\Services\ListingService;
use Database\Seeders\LocationSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

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

it('stores commercial badges and exposes only selected badges publicly', function (): void {
    $user = User::factory()->create();
    $category = categoryForListings();

    $this->actingAs($user)->post('/admin/anuncios', [
        'category_id' => $category->id,
        'title' => 'Bicicleta com condicoes especiais',
        'description' => 'Bicicleta revisada e pronta para retirada imediata.',
        'price' => '1200.00',
        'accepts_offers' => true,
        'quick_sale' => true,
        'negotiable_price' => false,
        'easy_pickup' => false,
        'city' => 'Maringa',
        'state' => 'PR',
        'contact_name' => 'Anunciante',
        'status' => ListingStatus::Published->value,
    ])->assertRedirect();

    $listing = Listing::query()->sole();

    expect($listing->accepts_offers)->toBeTrue()
        ->and($listing->quick_sale)->toBeTrue()
        ->and($listing->negotiable_price)->toBeFalse()
        ->and($listing->easy_pickup)->toBeFalse();

    $this->get('/anuncios')
        ->assertInertia(fn (Assert $page) => $page->where('listings.data.0.commercial_badges', [
            'Aceita proposta',
            'Venda rápida',
        ]));

    $this->get("/anuncios/{$listing->slug}")
        ->assertInertia(fn (Assert $page) => $page->where('listing.commercial_badges', [
            'Aceita proposta',
            'Venda rápida',
        ]));
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

    $this->post("/anuncios/{$listing->slug}/contato", [
        'name' => 'Comprador',
        'email' => 'buyer@example.com',
        'phone' => '(43) 98888-0000',
        'message' => 'Tenho interesse no produto anunciado.',
    ])->assertRedirect();

    Mail::assertSent(
        ListingContactMail::class,
        fn (ListingContactMail $mail): bool => $mail->hasTo('seller@example.com'),
    );
});

it('does not accept legacy listing ids for public contact', function (): void {
    $user = User::factory()->create();
    $category = categoryForListings();
    $listing = Listing::query()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Mesa para contato',
        'slug' => 'mesa-para-contato',
        'description' => 'Mesa publicada para validar a rota de contato.',
        'price_cents' => 45000,
        'city' => 'Maringa',
        'state' => 'PR',
        'contact_name' => 'Anunciante',
        'status' => ListingStatus::Published,
        'published_at' => now(),
    ]);

    $this->post("/anuncios/{$listing->id}/contato", [
        'name' => 'Comprador',
        'email' => 'buyer@example.com',
        'message' => 'Tenho interesse no produto anunciado.',
    ])->assertNotFound();
});

it('uses globally unique slugs for listings from different advertisers', function (): void {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();
    $category = categoryForListings();
    $data = [
        'category_id' => $category->id,
        'title' => 'Mesa de madeira',
        'description' => 'Mesa em madeira macica para sala de jantar.',
        'price' => '350.00',
        'city' => 'Maringa',
        'state' => 'PR',
        'contact_name' => 'Anunciante',
        'status' => ListingStatus::Draft,
    ];

    $firstListing = app(ListingService::class)->create($firstUser, $data);
    $secondListing = app(ListingService::class)->create($secondUser, $data);

    expect($firstListing->slug)->toBe('mesa-de-madeira')
        ->and($secondListing->slug)->toBe('mesa-de-madeira-2');
});

it('redirects legacy public listing ids to the canonical slug url', function (): void {
    $user = User::factory()->create();
    $category = categoryForListings();
    $listing = Listing::query()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Cadeira de escritorio',
        'slug' => 'cadeira-de-escritorio',
        'description' => 'Cadeira ergonomica em boas condicoes.',
        'price_cents' => 40000,
        'city' => 'Maringa',
        'state' => 'PR',
        'contact_name' => 'Anunciante',
        'status' => ListingStatus::Published,
        'published_at' => now(),
    ]);

    $this->get("/anuncios/{$listing->id}")
        ->assertRedirect("/anuncios/{$listing->slug}")
        ->assertStatus(301);
});

it('does not redirect legacy ids for listings that are not publicly visible', function (): void {
    $user = User::factory()->create();
    $category = categoryForListings();
    $draftListing = Listing::query()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Mesa em rascunho',
        'slug' => 'mesa-em-rascunho',
        'description' => 'Mesa ainda nao publicada.',
        'price_cents' => 30000,
        'city' => 'Maringa',
        'state' => 'PR',
        'contact_name' => 'Anunciante',
        'status' => ListingStatus::Draft,
    ]);
    $expiredListing = Listing::query()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Cadeira expirada',
        'slug' => 'cadeira-expirada',
        'description' => 'Cadeira com anuncio expirado.',
        'price_cents' => 20000,
        'city' => 'Maringa',
        'state' => 'PR',
        'contact_name' => 'Anunciante',
        'status' => ListingStatus::Published,
        'published_at' => now()->subDays(2),
        'expires_at' => now()->subDay(),
    ]);

    $this->get("/anuncios/{$draftListing->id}")->assertNotFound();
    $this->get("/anuncios/{$expiredListing->id}")->assertNotFound();
});

it('renders listing social meta tags in the initial html with the cover image', function (): void {
    Storage::fake('public');

    $user = User::factory()->create();
    $category = categoryForListings();
    $listing = Listing::query()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Bicicleta aro 29',
        'slug' => 'bicicleta-aro-29',
        'description' => 'Bicicleta seminova com freio a disco, pronta para uso urbano e trilhas leves.',
        'price_cents' => 120000,
        'city' => 'Jaragua do Sul',
        'state' => 'SC',
        'contact_name' => 'Anunciante',
        'status' => ListingStatus::Published,
        'published_at' => now(),
    ]);
    Storage::disk('public')->put('media/share.webp', 'image');
    $asset = MediaAsset::query()->create([
        'user_id' => $user->id,
        'disk' => 'public',
        'path' => 'media/share.webp',
        'original_name' => 'share.webp',
        'mime_type' => 'image/webp',
        'size' => 5,
        'kind' => 'image',
    ]);
    ListingImage::query()->create([
        'listing_id' => $listing->id,
        'media_asset_id' => $asset->id,
        'sort_order' => 1,
        'is_cover' => true,
    ]);

    $this->get("/anuncios/{$listing->slug}")->assertOk()
        ->assertSee('<meta property="og:title" content="Bicicleta aro 29 | Classificados">', false)
        ->assertSee('<meta property="og:description"', false)
        ->assertSee('<meta property="og:image" content="http://localhost/storage/media/share.webp">', false)
        ->assertSee('<meta property="og:url" content="http://localhost/anuncios/'.$listing->slug.'">', false)
        ->assertSee('<meta name="twitter:title" content="Bicicleta aro 29 | Classificados">', false)
        ->assertSee('<meta name="twitter:description"', false)
        ->assertSee('<meta name="twitter:image" content="http://localhost/storage/media/share.webp">', false);
});

it('uses the configured default social image when listing has no image', function (): void {
    config(['seo.default_image' => '/images/default-share.png']);

    $user = User::factory()->create();
    $category = categoryForListings();
    $listing = Listing::query()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Mesa lateral',
        'slug' => 'mesa-lateral',
        'description' => 'Mesa lateral em madeira para sala ou quarto.',
        'price_cents' => 8000,
        'city' => 'Jaragua do Sul',
        'state' => 'SC',
        'contact_name' => 'Anunciante',
        'status' => ListingStatus::Published,
        'published_at' => now(),
    ]);

    $this->get("/anuncios/{$listing->slug}")->assertOk()
        ->assertSee('<meta property="og:image" content="http://localhost/images/default-share.png">', false)
        ->assertSee('<meta name="twitter:image" content="http://localhost/images/default-share.png">', false);
});

it('ignores missing storage files when rendering public listing images', function (): void {
    Storage::fake('public');

    $user = User::factory()->create();
    $category = categoryForListings();
    $listing = Listing::query()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Renault Clio Campus',
        'slug' => 'renault-clio-campus',
        'description' => 'Carro conservado para venda local.',
        'price_cents' => 1800000,
        'city' => 'Jaragua do Sul',
        'state' => 'SC',
        'contact_name' => 'Anunciante',
        'status' => ListingStatus::Published,
        'published_at' => now(),
    ]);
    $missingAsset = MediaAsset::query()->create([
        'user_id' => $user->id,
        'disk' => 'public',
        'path' => 'media/2026/07/missing.webp',
        'original_name' => 'missing.webp',
        'mime_type' => 'image/webp',
        'size' => 5,
        'kind' => 'image',
    ]);
    Storage::disk('public')->put('media/2026/07/current.webp', 'image');
    $currentAsset = MediaAsset::query()->create([
        'user_id' => $user->id,
        'disk' => 'public',
        'path' => 'media/2026/07/current.webp',
        'original_name' => 'current.webp',
        'mime_type' => 'image/webp',
        'size' => 5,
        'kind' => 'image',
    ]);
    ListingImage::query()->create([
        'listing_id' => $listing->id,
        'media_asset_id' => $missingAsset->id,
        'sort_order' => 1,
        'is_cover' => true,
    ]);
    ListingImage::query()->create([
        'listing_id' => $listing->id,
        'media_asset_id' => $currentAsset->id,
        'sort_order' => 2,
        'is_cover' => false,
    ]);

    $this->get("/anuncios/{$listing->slug}")->assertOk()
        ->assertDontSee('/storage/media/2026/07/missing.webp')
        ->assertSee('/storage/media/2026/07/current.webp')
        ->assertSee('<meta property="og:image" content="http://localhost/storage/media/2026/07/current.webp">', false);
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

it('promotes the first available image when the current cover file is missing', function (): void {
    Storage::fake('public');

    $user = User::factory()->create();
    $category = categoryForListings();
    $listing = Listing::query()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Renault Clio',
        'slug' => 'renault-clio-cover',
        'description' => 'Carro conservado para venda local.',
        'price_cents' => 1800000,
        'city' => 'Jaragua do Sul',
        'state' => 'SC',
        'contact_name' => 'Anunciante',
        'status' => ListingStatus::Draft,
    ]);
    $missingAsset = MediaAsset::query()->create([
        'user_id' => $user->id,
        'disk' => 'public',
        'path' => 'media/missing-cover.webp',
        'original_name' => 'missing-cover.webp',
        'mime_type' => 'image/webp',
        'size' => 5,
        'kind' => 'image',
    ]);
    Storage::disk('public')->put('media/current-cover.webp', 'image');
    $currentAsset = MediaAsset::query()->create([
        'user_id' => $user->id,
        'disk' => 'public',
        'path' => 'media/current-cover.webp',
        'original_name' => 'current-cover.webp',
        'mime_type' => 'image/webp',
        'size' => 5,
        'kind' => 'image',
    ]);
    $missingImage = ListingImage::query()->create([
        'listing_id' => $listing->id,
        'media_asset_id' => $missingAsset->id,
        'sort_order' => 1,
        'is_cover' => true,
    ]);
    $currentImage = ListingImage::query()->create([
        'listing_id' => $listing->id,
        'media_asset_id' => $currentAsset->id,
        'sort_order' => 2,
        'is_cover' => false,
    ]);

    app(ListingImageService::class)->ensureCoverImage($listing);

    expect($missingImage->fresh()->is_cover)->toBeFalse()
        ->and($currentImage->fresh()->is_cover)->toBeTrue();
});

it('marks a new upload as cover when the existing cover file is missing', function (): void {
    Storage::fake('public');

    $user = User::factory()->create();
    $category = categoryForListings();
    $listing = Listing::query()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Renault Clio Upload',
        'slug' => 'renault-clio-upload',
        'description' => 'Carro conservado para venda local.',
        'price_cents' => 1800000,
        'city' => 'Jaragua do Sul',
        'state' => 'SC',
        'contact_name' => 'Anunciante',
        'status' => ListingStatus::Draft,
    ]);
    $missingAsset = MediaAsset::query()->create([
        'user_id' => $user->id,
        'disk' => 'public',
        'path' => 'media/missing-upload-cover.webp',
        'original_name' => 'missing-upload-cover.webp',
        'mime_type' => 'image/webp',
        'size' => 5,
        'kind' => 'image',
    ]);
    $missingImage = ListingImage::query()->create([
        'listing_id' => $listing->id,
        'media_asset_id' => $missingAsset->id,
        'sort_order' => 1,
        'is_cover' => true,
    ]);

    app(ListingImageService::class)->attachUploadedImages($listing, $user, [
        UploadedFile::fake()->image('current.jpg', 640, 480),
    ]);

    $newImage = ListingImage::query()
        ->where('listing_id', $listing->id)
        ->whereKeyNot($missingImage->id)
        ->sole();

    expect($missingImage->fresh()->is_cover)->toBeFalse()
        ->and($newImage->is_cover)->toBeTrue();
});

it('ignores listing images without media assets when serializing', function (): void {
    $listing = new Listing;
    $listing->setRelation('images', collect([
        new ListingImage(['id' => 1, 'is_cover' => true]),
    ]));

    $images = app(ListingImageService::class)->serializeImages($listing);

    expect($images)->toHaveCount(0);
});

it('ignores listing images with missing storage files when serializing', function (): void {
    Storage::fake('public');

    $user = User::factory()->create();
    $listing = new Listing;
    $missingAsset = MediaAsset::query()->create([
        'user_id' => $user->id,
        'disk' => 'public',
        'path' => 'media/missing.webp',
        'original_name' => 'missing.webp',
        'mime_type' => 'image/webp',
        'size' => 5,
        'kind' => 'image',
    ]);
    $listing->setRelation('images', collect([
        new ListingImage(['id' => 1, 'media_asset_id' => $missingAsset->id, 'is_cover' => true]),
    ]));
    $listing->images->first()->setRelation('mediaAsset', $missingAsset);

    $images = app(ListingImageService::class)->serializeImages($listing);

    expect($images)->toHaveCount(0);
});
