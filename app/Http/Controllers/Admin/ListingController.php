<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ListingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreListingRequest;
use App\Http\Requests\UpdateListingRequest;
use App\Models\Category;
use App\Models\Listing;
use App\Services\ListingImageService;
use App\Services\ListingService;
use App\Services\LocationOptionsService;
use App\Support\Seo\SeoData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListingController extends Controller
{
    public function index(Request $request, ListingImageService $images): Response
    {
        $filters = $request->only(['status', 'q']);
        $listings = Listing::query()
            ->whereBelongsTo($request->user())
            ->with(['category', 'images.mediaAsset'])
            ->when(
                $filters['status'] ?? null,
                fn ($query, $status) => $query->where('status', $status)
            )
            ->when($filters['q'] ?? null, function ($query, $term): void {
                $query->where(function ($query) use ($term): void {
                    $query->where('title', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Listings/Index', [
            'filters' => $filters,
            'statuses' => $this->statuses(),
            'listings' => $listings->through(
                fn (Listing $listing): array => $this->serializeListing($listing, $images)
            ),
            'seo' => SeoData::privatePage('Meus anuncios')->toArray(),
        ]);
    }

    public function create(LocationOptionsService $locations): Response
    {
        return Inertia::render('Admin/Listings/Create', [
            'categories' => $this->categories(),
            'cities' => $locations->cities(),
            'states' => $locations->states(),
            'statuses' => $this->statuses(),
            'seo' => SeoData::privatePage('Novo anuncio')->toArray(),
        ]);
    }

    public function store(StoreListingRequest $request, ListingService $service): RedirectResponse
    {
        $listing = $service->create($request->user(), $request->validated());

        return redirect()
            ->route('admin.listings.edit', $listing)
            ->with('success', 'Anuncio salvo.');
    }

    public function edit(
        Listing $listing,
        ListingImageService $images,
        LocationOptionsService $locations,
    ): Response {
        $this->authorize('update', $listing);
        $listing->load(['category', 'images.mediaAsset']);

        return Inertia::render('Admin/Listings/Edit', [
            'listing' => [
                ...$this->serializeListing($listing, $images),
                'description' => $listing->description,
                'category_id' => $listing->category_id,
                'contact_name' => $listing->contact_name,
                'contact_email' => $listing->contact_email,
                'contact_phone' => $listing->contact_phone,
                'expires_at' => $listing->expires_at?->toDateString(),
                'images' => $images->serializeImages($listing),
            ],
            'categories' => $this->categories(),
            'cities' => $locations->cities(),
            'states' => $locations->states(),
            'statuses' => $this->statuses(),
            'seo' => SeoData::privatePage('Editar anuncio')->toArray(),
        ]);
    }

    public function update(
        UpdateListingRequest $request,
        Listing $listing,
        ListingService $service,
    ): RedirectResponse {
        $this->authorize('update', $listing);
        $service->update($listing, $request->validated());

        return back()->with('success', 'Anuncio atualizado.');
    }

    public function destroy(Listing $listing): RedirectResponse
    {
        $this->authorize('delete', $listing);
        $listing->delete();

        return redirect()
            ->route('admin.listings.index')
            ->with('success', 'Anuncio removido.');
    }

    private function serializeListing(Listing $listing, ListingImageService $images): array
    {
        return [
            'id' => $listing->id,
            'title' => $listing->title,
            'category' => $listing->category?->name,
            'price' => $listing->formattedPrice(),
            'price_value' => number_format($listing->price_cents / 100, 2, '.', ''),
            'city' => $listing->city,
            'state' => $listing->state,
            'status' => $listing->status->value,
            'status_label' => $listing->status->label(),
            'published_at' => $listing->published_at?->toDateString(),
            'cover_url' => $images->coverUrl($listing),
            'is_featured' => $listing->id === auth()->user()?->featured_listing_id,
            'edit_url' => route('admin.listings.edit', $listing),
            'publish_url' => route('admin.listings.publish', $listing),
            'public_url' => $listing->status->isPublic() ? route('listings.show', $listing->slug) : null,
        ];
    }

    private function categories(): array
    {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->all();
    }

    private function statuses(): array
    {
        return collect(ListingStatus::cases())
            ->map(fn (ListingStatus $status): array => [
                'value' => $status->value,
                'label' => $status->label(),
            ])
            ->all();
    }
}
