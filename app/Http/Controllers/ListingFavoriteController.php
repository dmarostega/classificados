<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\User;
use App\Services\ListingFavoriteService;
use App\Services\ListingImageService;
use App\Support\Seo\SeoData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListingFavoriteController extends Controller
{
    public function index(Request $request, ListingImageService $images): Response
    {
        /** @var User $user */
        $user = $request->user();
        $listings = $user->favoriteListings()
            ->public()
            ->with(['category', 'images.mediaAsset'])
            ->orderByPivot('created_at', 'desc')
            ->paginate(12);

        return Inertia::render('Favorites/Index', [
            'listings' => $listings->through(
                fn (Listing $listing): array => $this->listingCard($listing, $images)
            ),
            'seo' => SeoData::privatePage('Meus favoritos')->toArray(),
        ]);
    }

    public function store(
        Request $request,
        Listing $listing,
        ListingFavoriteService $favorites,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();
        $favorites->add($user, $listing);

        return back()->with('success', 'Anuncio adicionado aos favoritos.');
    }

    public function destroy(
        Request $request,
        Listing $listing,
        ListingFavoriteService $favorites,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();
        $favorites->remove($user, $listing);

        return back()->with('success', 'Anuncio removido dos favoritos.');
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
            'url' => route('listings.show', $listing->slug),
            'cover_url' => $images->coverUrl($listing),
            'is_favorited' => true,
        ];
    }
}
