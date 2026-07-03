<?php

use App\Enums\ListingStatus;
use App\Mail\ListingContactMail;
use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

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
        'state' => 'PR',
        'contact_name' => 'Dono do anuncio',
        'contact_email' => 'vendedor@example.com',
        'contact_phone' => '(44) 99999-9999',
        'status' => ListingStatus::Published->value,
    ])->assertRedirect();

    $listing = Listing::query()->sole();

    expect($listing->user_id)->toBe($user->id)
        ->and($listing->category_id)->toBe($category->id)
        ->and($listing->price_cents)->toBe(7990000)
        ->and($listing->published_at)->not->toBeNull();
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
