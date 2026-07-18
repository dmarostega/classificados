<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Services\Marketplaces\MarketplaceDraftService;
use App\Support\Seo\SeoData;
use Inertia\Inertia;
use Inertia\Response;

class MarketplaceListingController extends Controller
{
    public function show(Listing $listing, MarketplaceDraftService $drafts): Response
    {
        $this->authorize('view', $listing);
        $listing->load('category');

        return Inertia::render('Admin/Listings/Publish', [
            'listing' => [
                'title' => $listing->title,
                'price' => $listing->formattedPrice(),
                'category' => $listing->category?->name,
                'location' => "{$listing->city} / {$listing->state}",
                'description' => $listing->description,
                'edit_url' => route('admin.listings.edit', $listing),
                'public_url' => $listing->isPubliclyVisible() ? $listing->publicUrl() : null,
            ],
            'drafts' => $drafts->draftsFor($listing),
            'seo' => SeoData::privatePage('Preparar publicacao')->toArray(),
        ]);
    }
}
