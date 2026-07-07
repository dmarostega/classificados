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
    public function index(Request $request, LocationOptionsService $locations): Response
    {
        $filters = $request->only(['category', 'city', 'state', 'q']);
        $listings = Listing::query()
            ->public()
            ->with(['category', 'images.mediaAsset'])
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
            'listings' => $listings->through(fn (Listing $listing): array => $this->listingCard($listing)),
            'seo' => SeoData::page(
                'Classificados',
                'Encontre anuncios publicados por vendedores locais.',
            )->toArray(),
            'states' => $locations->states(),
        ]);
    }

    public function show(Listing $listing, ListingImageService $images): Response
    {
        abort_unless($listing->isPubliclyVisible(), 404);

        $listing->increment('views_count');
        $listing->load(['category', 'user', 'images.mediaAsset']);

        $listingCard = $this->listingCard($listing);

        return Inertia::render('Public/Listings/Show', [
            'listing' => [
                ...$listingCard,
                'description' => $listing->description,
                'contact_name' => $listing->contact_name,
                'contact_phone' => $listing->contact_phone,
                'advertiser' => [
                    'name' => $listing->user->name,
                    'url' => route('advertisers.show', $listing->user),
                ],
                'images' => $images->serializeImages($listing),
                'views_count' => $listing->views_count,
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

    private function listingCard(Listing $listing): array
    {
        $cover = $listing->images->firstWhere('is_cover', true) ?: $listing->images->first();

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
            'cover_url' => $cover?->mediaAsset?->url,
        ];
    }
}
