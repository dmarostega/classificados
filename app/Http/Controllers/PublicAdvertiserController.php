<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\User;
use App\Services\ListingImageService;
use App\Support\Seo\SeoData;
use Inertia\Inertia;
use Inertia\Response;

class PublicAdvertiserController extends Controller
{
    public function __invoke(User $user, ListingImageService $images): Response
    {
        $listings = Listing::query()
            ->public()
            ->whereBelongsTo($user)
            ->with(['category', 'images.mediaAsset'])
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        abort_if($listings->total() === 0, 404);

        $firstListing = $listings->getCollection()->first();

        return Inertia::render('Public/Advertisers/Show', [
            'advertiser' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
            'listings' => $listings->through(fn (Listing $listing): array => $this->listingCard($listing, $images)),
            'seo' => SeoData::page(
                "Anuncios de {$user->name}",
                "Veja anuncios publicados por {$user->name} no Classificados.",
                $firstListing ? $images->coverUrl($firstListing) : null,
            )->toArray(),
        ]);
    }

    private function listingCard(Listing $listing, ListingImageService $images): array
    {
        return [
            'id' => $listing->id,
            'title' => $listing->title,
            'slug' => $listing->slug,
            'category' => $listing->category?->name,
            'price' => $listing->formattedPrice(),
            'city' => $listing->city,
            'state' => $listing->state,
            'published_at' => $listing->published_at?->toDateString(),
            'url' => route('listings.show', $listing),
            'cover_url' => $images->coverUrl($listing),
        ];
    }
}
