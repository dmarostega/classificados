<?php

namespace App\Http\Controllers;

use App\Models\ListingFavorite;
use Illuminate\Http\RedirectResponse;

class FavoriteNotificationController extends Controller
{
    public function __invoke(ListingFavorite $favorite): RedirectResponse
    {
        $favorite->update(['email_notifications_enabled' => false]);

        return redirect()
            ->route('home')
            ->with('success', 'Notificacoes por e-mail desativadas para este anuncio.');
    }
}
