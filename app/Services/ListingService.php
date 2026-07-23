<?php

namespace App\Services;

use App\Enums\ListingStatus;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ListingService
{
    private const NOTIFIABLE_FIELDS = [
        'category_id',
        'title',
        'description',
        'price_cents',
        'city',
        'state',
    ];

    public function __construct(
        private readonly ListingImageService $images,
        private readonly FavoriteListingNotificationService $notifications,
    ) {}

    public function create(User $user, array $data): Listing
    {
        return DB::transaction(function () use ($user, $data): Listing {
            $listing = Listing::create($this->payload($user, $data));
            $this->images->attachUploadedImages($listing, $user, Arr::wrap($data['images'] ?? []));

            return $listing->fresh(['category', 'images.mediaAsset']);
        });
    }

    public function update(Listing $listing, array $data): Listing
    {
        $wasPubliclyVisible = $listing->isPubliclyVisible();
        $originalImageIds = $listing->images()->pluck('id')->all();
        $hasRelevantChanges = false;

        $updatedListing = DB::transaction(function () use (
            $listing,
            $data,
            $originalImageIds,
            &$hasRelevantChanges,
        ): Listing {
            $listing->update($this->payload($listing->user, $data, $listing));
            $hasRelevantChanges = $listing->wasChanged(self::NOTIFIABLE_FIELDS);

            if (! empty($data['remove_image_ids'])) {
                $this->images->deleteImages($listing, Arr::wrap($data['remove_image_ids']));
            }

            $this->images->attachUploadedImages($listing, $listing->user, Arr::wrap($data['images'] ?? []));
            $currentImageIds = $listing->images()->pluck('id')->all();
            $hasRelevantChanges = $hasRelevantChanges || $originalImageIds !== $currentImageIds;

            return $listing->fresh(['category', 'images.mediaAsset']);
        });

        if ($wasPubliclyVisible && $updatedListing->isPubliclyVisible() && $hasRelevantChanges) {
            $this->notifications->dispatchFor($updatedListing);
        }

        return $updatedListing;
    }

    private function payload(User $user, array $data, ?Listing $listing = null): array
    {
        $status = $data['status'] instanceof ListingStatus ? $data['status'] : ListingStatus::from($data['status']);
        $publishedAt = $listing?->published_at;

        if ($status->isPublic() && ! $publishedAt) {
            $publishedAt = Carbon::now();
        }

        if (! $status->isPublic()) {
            $publishedAt = null;
        }

        return [
            'user_id' => $user->id,
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($data['title'], $listing),
            'description' => $data['description'],
            'price_cents' => (int) round(((float) $data['price']) * 100),
            'accepts_offers' => $data['accepts_offers'] ?? $listing?->accepts_offers ?? false,
            'quick_sale' => $data['quick_sale'] ?? $listing?->quick_sale ?? false,
            'negotiable_price' => $data['negotiable_price'] ?? $listing?->negotiable_price ?? false,
            'easy_pickup' => $data['easy_pickup'] ?? $listing?->easy_pickup ?? false,
            'is_reserved' => $data['is_reserved'] ?? $listing?->is_reserved ?? false,
            'city' => $data['city'],
            'state' => Str::upper($data['state']),
            'contact_name' => $data['contact_name'],
            'contact_email' => $data['contact_email'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
            'status' => $status,
            'published_at' => $publishedAt,
            'expires_at' => $data['expires_at'] ?? null,
        ];
    }

    private function uniqueSlug(string $title, ?Listing $listing = null): string
    {
        $base = Str::slug($title) ?: Str::lower((string) Str::ulid());

        if (ctype_digit($base)) {
            $base = "anuncio-{$base}";
        }

        $slug = $base;
        $suffix = 2;

        while (Listing::where('slug', $slug)
            ->when($listing, fn ($query) => $query->whereKeyNot($listing->id))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
