<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Support\Seo\SeoData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $query = Listing::query()->whereBelongsTo($request->user());

        return Inertia::render('Dashboard', [
            'metrics' => [
                'total' => (clone $query)->count(),
                'published' => (clone $query)->where('status', 'published')->count(),
                'drafts' => (clone $query)->where('status', 'draft')->count(),
                'views' => (clone $query)->sum('views_count'),
            ],
            'latestListings' => (clone $query)
                ->with('category')
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (Listing $listing): array => [
                    'id' => $listing->id,
                    'title' => $listing->title,
                    'status' => $listing->status->label(),
                    'category' => $listing->category?->name,
                    'price' => $listing->formattedPrice(),
                    'edit_url' => route('admin.listings.edit', $listing),
                ]),
            'seo' => SeoData::privatePage('Painel')->toArray(),
        ]);
    }
}
