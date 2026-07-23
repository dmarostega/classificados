<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactListingRequest;
use App\Mail\ListingContactMail;
use App\Models\Category;
use App\Models\Listing;
use App\Services\ListingImageService;
use App\Services\LocationOptionsService;
use App\Support\Seo\SeoData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class PublicListingController extends Controller
{
    public function index(
        Request $request,
        LocationOptionsService $locations,
        ListingImageService $images,
    ): Response {
        $filters = $request->only(['category', 'city', 'state', 'q']);
        $listings = Listing::query()
            ->public()
            ->with(['category', 'images.mediaAsset'])
            ->when(
                $request->user(),
                fn ($query, $user) => $query->withExists([
                    'favorites as is_favorited' => fn ($query) => $query->where('user_id', $user->id),
                ])
            )
            ->when(
                $filters['category'] ?? null,
                fn ($query, $slug) => $query->whereRelation('category', 'slug', $slug)
            )
            ->when($filters['city'] ?? null, fn ($query, $city) => $query->where('city', $city))
            ->when(
                $filters['state'] ?? null,
                fn ($query, $state) => $query->where('state', strtoupper($state))
            )
            ->when($filters['q'] ?? null, function ($query, $term): void {
                $query->where(function ($query) use ($term): void {
                    $query->where('title', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                });
            })
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Public/Listings/Index', [
            'categories' => Category::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
            'cities' => $locations->cities(),
            'filters' => $filters,
            'listings' => $listings->through(fn (Listing $listing): array => $this->listingCard($listing, $images)),
            'seo' => SeoData::page(
                'Classificados',
                'Encontre anuncios publicados por vendedores locais.',
            )->toArray(),
            'states' => $locations->states(),
        ]);
    }

    public function show(Request $request, string $listing, ListingImageService $images): Response|RedirectResponse
    {
        if (ctype_digit($listing)) {
            $listing = Listing::query()->findOrFail($listing);

            abort_unless($listing->isPubliclyVisible(), 404);

            return redirect()->route('listings.show', $listing->slug, 301);
        }

        $listing = Listing::query()->where('slug', $listing)->firstOrFail();
        abort_unless($listing->isPubliclyVisible(), 404);

        $listing->increment('views_count');
        $listing->load(['category', 'user', 'images.mediaAsset']);

        $listingCard = $this->listingCard($listing, $images);

        return Inertia::render('Public/Listings/Show', [
            'listing' => [
                ...$listingCard,
                'description' => $listing->description,
                'contact_name' => $listing->contact_name,
                'contact_phone_masked' => $listing->maskedContactPhone(),
                'advertiser' => [
                    'name' => $listing->user->name,
                    'url' => route('advertisers.show', $listing->user->slug),
                ],
                'images' => $images->serializeImages($listing),
                'views_count' => $listing->views_count,
                'is_favorited' => $request->user()
                    ? $listing->favorites()->whereBelongsTo($request->user())->exists()
                    : false,
            ],
            'seo' => SeoData::page(
                $listing->title,
                str($listing->description)->limit(150)->toString(),
                $listingCard['cover_url'],
            )->toArray(),
        ]);
    }

    public function contact(ContactListingRequest $request, Listing $listing): RedirectResponse
    {
        abort_unless($listing->isPubliclyVisible(), 404);

        $recipient = $listing->contact_email ?: $listing->user->email;
        Mail::to($recipient)->send(new ListingContactMail($listing, $request->validated()));

        return back()->with('success', 'Mensagem enviada ao anunciante.');
    }

    private function listingCard(Listing $listing, ListingImageService $images): array
    {
        return [
            'id' => $listing->id,
            'title' => $listing->title,
            'slug' => $listing->slug,
            'category' => $listing->category?->name,
            'price' => $listing->formattedPrice(),
            'commercial_badges' => $listing->commercialBadges(),
            'city' => $listing->city,
            'state' => $listing->state,
            'published_at' => $listing->published_at?->toDateString(),
            'url' => route('listings.show', $listing->slug),
            'cover_url' => $images->coverUrl($listing),
            'is_favorited' => (bool) ($listing->is_favorited ?? false),
        ];
    }
}
