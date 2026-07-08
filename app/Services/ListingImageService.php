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
        $hasCover = $this->hasAvailableCoverImage($listing);

        if (! $hasCover) {
            $this->clearCoverFlags($listing);
        }

        foreach ($files as $file) {
            $asset = $this->media->store($file, $user, $listing->title);
            $isCover = ! $hasCover;

            ListingImage::create([
                'listing_id' => $listing->id,
                'media_asset_id' => $asset->id,
                'sort_order' => $nextOrder,
                'is_cover' => $isCover,
            ]);

            $hasCover = true;
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
                $asset = $image->mediaAsset;

                $image->delete();

                if ($asset) {
                    $this->media->delete($asset);
                }
            });

        $this->ensureCoverImage($listing);
    }

    public function ensureCoverImage(Listing $listing): void
    {
        if ($this->hasAvailableCoverImage($listing)) {
            return;
        }

        $this->clearCoverFlags($listing);

        $first = $this->availableImagesFromQuery($listing)->first();

        if ($first) {
            $first->update(['is_cover' => true]);
        }
    }

    public function serializeImages(Listing $listing): Collection
    {
        return $this->availableImages($listing)
            ->map(fn (ListingImage $image): array => [
                'id' => $image->id,
                'url' => $image->mediaAsset->url,
                'alt_text' => $image->mediaAsset->alt_text,
                'is_cover' => $image->is_cover,
            ])
            ->values();
    }

    public function coverUrl(Listing $listing): ?string
    {
        $images = $this->availableImages($listing);
        $cover = $images->firstWhere('is_cover', true) ?: $images->first();

        return $cover?->mediaAsset?->url;
    }

    private function availableImages(Listing $listing): Collection
    {
        return $this->filterAvailableImages($listing->images);
    }

    private function availableImagesFromQuery(Listing $listing, bool $onlyCover = false): Collection
    {
        $query = $listing->images()->with('mediaAsset');

        if ($onlyCover) {
            $query->where('is_cover', true);
        }

        return $this->filterAvailableImages($query->get());
    }

    private function filterAvailableImages(Collection $images): Collection
    {
        return $images
            ->filter(
                fn (ListingImage $image): bool => $image->mediaAsset !== null
                    && $image->mediaAsset->existsOnDisk()
            )
            ->values();
    }

    private function hasAvailableCoverImage(Listing $listing): bool
    {
        return $this->availableImagesFromQuery($listing, onlyCover: true)->isNotEmpty();
    }

    private function clearCoverFlags(Listing $listing): void
    {
        $listing->images()->where('is_cover', true)->update(['is_cover' => false]);
    }
}
