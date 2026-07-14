<?php

namespace App\Services;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListingPhoneRevealService
{
    /**
     * @return array{phone: string, phone_href: string, whatsapp_url: string}
     */
    public function reveal(Listing $listing, Request $request): array
    {
        abort_unless($listing->isPubliclyVisible(), 404);

        $phone = trim((string) $listing->contact_phone);
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        abort_if($phone === '' || $digits === '', 404);

        DB::table('listing_phone_reveals')->insert([
            'listing_id' => $listing->id,
            'request_fingerprint' => $this->requestFingerprint($request),
            'created_at' => now(),
        ]);

        $internationalDigits = $this->internationalDigits($digits);

        return [
            'phone' => $phone,
            'phone_href' => 'tel:+'.$internationalDigits,
            'whatsapp_url' => 'https://wa.me/'.$internationalDigits,
        ];
    }

    private function requestFingerprint(Request $request): ?string
    {
        $technicalContext = array_filter([$request->ip(), $request->userAgent()]);

        if ($technicalContext === []) {
            return null;
        }

        return hash_hmac('sha256', implode('|', $technicalContext), (string) config('app.key'));
    }

    private function internationalDigits(string $digits): string
    {
        if (in_array(strlen($digits), [10, 11], true)) {
            return '55'.$digits;
        }

        return $digits;
    }
}
