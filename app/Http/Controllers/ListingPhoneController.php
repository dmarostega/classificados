<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Services\ListingPhoneRevealService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListingPhoneController extends Controller
{
    public function __invoke(
        Listing $listing,
        Request $request,
        ListingPhoneRevealService $service,
    ): JsonResponse {
        return response()->json(
            $service->reveal($listing, $request),
            headers: ['Cache-Control' => 'no-store, private'],
        );
    }
}
