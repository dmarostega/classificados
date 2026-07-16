<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ListingFeaturedController extends Controller
{
    public function store(Request $request, Listing $listing): RedirectResponse
    {
        $this->authorize('update', $listing);

        if (! $listing->isPubliclyVisible()) {
            throw ValidationException::withMessages(['listing' => 'Apenas anuncios publicados e ativos podem receber destaque.']);
        }

        $request->user()->update(['featured_listing_id' => $listing->id]);

        return back()->with('success', 'Anuncio definido como destaque.');
    }

    public function destroy(Request $request, Listing $listing): RedirectResponse
    {
        $this->authorize('update', $listing);
        $request->user()->update(['featured_listing_id' => null]);

        return back()->with('success', 'Destaque removido.');
    }
}
