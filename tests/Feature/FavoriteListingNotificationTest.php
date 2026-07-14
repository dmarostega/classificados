<?php

use App\Enums\ListingStatus;
use App\Jobs\SendFavoriteListingUpdatedMail;
use App\Mail\FavoriteListingUpdatedMail;
use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingFavorite;
use App\Models\User;
use App\Services\ListingService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;

beforeEach(function (): void {
    $this->category = Category::query()->create([
        'name' => 'Eletronicos',
        'slug' => 'eletronicos-notificacoes',
        'is_active' => true,
        'sort_order' => 10,
    ]);
});

it('queues notifications only for eligible favorites after a relevant public listing change', function (): void {
    Queue::fake();

    $owner = User::factory()->create();
    $interested = User::factory()->create();
    $optedOut = User::factory()->create();
    $listing = favoriteNotificationListing($owner, $this->category);
    $eligibleFavorite = favoriteNotification($interested, $listing);
    favoriteNotification($optedOut, $listing, ['email_notifications_enabled' => false]);
    favoriteNotification($owner, $listing);

    app(ListingService::class)->update($listing, favoriteNotificationUpdateData($listing, [
        'price' => '1450.00',
    ]));

    Queue::assertPushed(
        SendFavoriteListingUpdatedMail::class,
        fn (SendFavoriteListingUpdatedMail $job): bool => $job->favoriteId === $eligibleFavorite->id,
    );
    Queue::assertPushed(SendFavoriteListingUpdatedMail::class, 1);
});

it('does not queue notifications for irrelevant changes or listings that leave public visibility', function (): void {
    Queue::fake();

    $owner = User::factory()->create();
    $listing = favoriteNotificationListing($owner, $this->category);
    favoriteNotification(User::factory()->create(), $listing);

    app(ListingService::class)->update($listing, favoriteNotificationUpdateData($listing, [
        'contact_name' => 'Novo contato do anunciante',
    ]));

    Queue::assertNothingPushed();

    app(ListingService::class)->update($listing->fresh(), favoriteNotificationUpdateData($listing->fresh(), [
        'title' => 'Notebook temporariamente pausado',
        'status' => ListingStatus::Paused,
    ]));

    Queue::assertNothingPushed();
});

it('unsubscribes a favorite through a signed link and rejects tampered links', function (): void {
    $favorite = favoriteNotification(
        User::factory()->create(),
        favoriteNotificationListing(User::factory()->create(), $this->category),
    );

    $this->get(URL::signedRoute('favorite-notifications.unsubscribe', ['favorite' => $favorite->id]))
        ->assertRedirect(route('home'));

    expect($favorite->fresh()->email_notifications_enabled)->toBeFalse();

    $favorite->update(['email_notifications_enabled' => true]);

    $this->get(route('favorite-notifications.unsubscribe', [
        'favorite' => $favorite->id,
        'signature' => 'invalid',
    ]))->assertForbidden();

    expect($favorite->fresh()->email_notifications_enabled)->toBeTrue();
});

it('sends an individual email and suppresses repeated sends during the cooldown', function (): void {
    Mail::fake();

    $favorite = favoriteNotification(
        User::factory()->create(['email' => 'interested@example.com']),
        favoriteNotificationListing(User::factory()->create(), $this->category),
    );
    $job = new SendFavoriteListingUpdatedMail($favorite->id);

    $job->handle();
    $job->handle();

    Mail::assertSent(
        FavoriteListingUpdatedMail::class,
        fn (FavoriteListingUpdatedMail $mail): bool => $mail->hasTo('interested@example.com'),
    );
    Mail::assertSentCount(1);
    expect($favorite->fresh()->last_notification_sent_at)->not->toBeNull();

    $renderedEmail = (new FavoriteListingUpdatedMail($favorite->fresh(['listing', 'user'])))->render();
    expect($renderedEmail)
        ->not->toContain('owner@example.com')
        ->not->toContain('interested@example.com');

    $this->travel(11)->minutes();
    $job->handle();

    Mail::assertSentCount(2);
});

it('rechecks opt-out and advertiser ownership when the queued job executes', function (): void {
    Mail::fake();

    $owner = User::factory()->create();
    $listing = favoriteNotificationListing($owner, $this->category);
    $optedOut = favoriteNotification(
        User::factory()->create(),
        $listing,
        ['email_notifications_enabled' => false],
    );
    $ownerFavorite = favoriteNotification($owner, $listing);

    (new SendFavoriteListingUpdatedMail($optedOut->id))->handle();
    (new SendFavoriteListingUpdatedMail($ownerFavorite->id))->handle();

    Mail::assertNothingSent();
});

function favoriteNotificationListing(User $owner, Category $category): Listing
{
    return Listing::query()->create([
        'user_id' => $owner->id,
        'category_id' => $category->id,
        'title' => 'Notebook para trabalho',
        'slug' => 'notebook-para-trabalho-'.$owner->id,
        'description' => 'Notebook conservado, com carregador e pronto para uso profissional.',
        'price_cents' => 120000,
        'city' => 'Maringa',
        'state' => 'PR',
        'contact_name' => 'Anunciante',
        'contact_email' => 'owner@example.com',
        'status' => ListingStatus::Published,
        'published_at' => now(),
    ]);
}

function favoriteNotification(User $user, Listing $listing, array $overrides = []): ListingFavorite
{
    return ListingFavorite::query()->create([
        'user_id' => $user->id,
        'listing_id' => $listing->id,
        ...$overrides,
    ]);
}

function favoriteNotificationUpdateData(Listing $listing, array $overrides = []): array
{
    return [
        'category_id' => $listing->category_id,
        'title' => $listing->title,
        'description' => $listing->description,
        'price' => number_format($listing->price_cents / 100, 2, '.', ''),
        'city' => $listing->city,
        'state' => $listing->state,
        'contact_name' => $listing->contact_name,
        'contact_email' => $listing->contact_email,
        'contact_phone' => $listing->contact_phone,
        'status' => $listing->status,
        'expires_at' => $listing->expires_at,
        ...$overrides,
    ];
}
