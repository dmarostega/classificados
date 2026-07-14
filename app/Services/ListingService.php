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
    public function __construct(private readonly ListingImageService $images) {}

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
        return DB::transaction(function () use ($listing, $data): Listing {
            $listing->update($this->payload($listing->user, $data, $listing));

            if (! empty($data['remove_image_ids'])) {
                $this->images->deleteImages($listing, Arr::wrap($data['remove_image_ids']));
            }

            $this->images->attachUploadedImages($listing, $listing->user, Arr::wrap($data['images'] ?? []));

            return $listing->fresh(['category', 'images.mediaAsset']);
        });
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
