<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class ListingImageService
{
    public function __construct(private readonly MediaService $media) {}

    /** @param array<int, UploadedFile> $files */
    public function attachUploadedImages(Listing $listing, User $user, array $files): void
    {
        $nextOrder = (int) $listing->images()->max('sort_order') + 1;
        $hasCover = $listing->images()->where('is_cover', true)->exists();

        foreach ($files as $file) {
            $asset = $this->media->store($file, $user, $listing->title);

            ListingImage::create([
                'listing_id' => $listing->id,
                'media_asset_id' => $asset->id,
                'sort_order' => $nextOrder,
                'is_cover' => ! $hasCover && $nextOrder === 1,
            ]);

            $nextOrder++;
        }
    }

    /** @param array<int, int> $ids */
    public function deleteImages(Listing $listing, array $ids): void
    {
        $listing->images()
            ->with('mediaAsset')
            ->whereIn('id', $ids)
            ->get()
            ->each(function (ListingImage $image): void {
                if ($image->mediaAsset) {
                    $this->media->delete($image->mediaAsset);
                }
            });

        $this->ensureCoverImage($listing);
    }

    public function ensureCoverImage(Listing $listing): void
    {
        if ($listing->images()->where('is_cover', true)->exists()) {
            return;
        }

        $first = $listing->images()->orderBy('sort_order')->first();

        if ($first) {
            $first->update(['is_cover' => true]);
        }
    }

    public function serializeImages(Listing $listing): Collection
    {
        return $listing->images->map(fn (ListingImage $image): array => [
            'id' => $image->id,
            'url' => $image->mediaAsset->url,
            'alt_text' => $image->mediaAsset->alt_text,
            'is_cover' => $image->is_cover,
        ]);
    }
}
