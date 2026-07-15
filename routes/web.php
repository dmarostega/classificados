<?php

use App\Http\Controllers\Admin\ListingController;
use App\Http\Controllers\AuthPageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FavoriteNotificationController;
use App\Http\Controllers\GrowthEventController;
use App\Http\Controllers\ListingFavoriteController;
use App\Http\Controllers\ListingPhoneController;
use App\Http\Controllers\MediaAssetController;
use App\Http\Controllers\PublicAdvertiserController;
use App\Http\Controllers\PublicListingController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicListingController::class, 'index'])->name('home');
Route::get('/anuncios', [PublicListingController::class, 'index'])->name('listings.index');
Route::get('/anuncios/{listing:slug}/telefone', ListingPhoneController::class)
    ->middleware('throttle:10,1')
    ->name('listings.phone.show');
Route::get('/anuncios/{listing}', [PublicListingController::class, 'show'])->name('listings.show');
Route::get('/anunciantes/{advertiser}', PublicAdvertiserController::class)->name('advertisers.show');
Route::post('/anuncios/{listing:slug}/contato', [PublicListingController::class, 'contact'])->middleware('throttle:10,1')->name('listings.contact');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::post('/growth/events', [GrowthEventController::class, 'store'])->middleware('throttle:120,1')->name('growth.events.store');
Route::get('/favoritos/notificacoes/{favorite}/cancelar', FavoriteNotificationController::class)
    ->middleware('signed')
    ->name('favorite-notifications.unsubscribe');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthPageController::class, 'login'])->name('login');
    Route::get('/register', [AuthPageController::class, 'register'])->name('register');
    Route::get('/forgot-password', [AuthPageController::class, 'forgotPassword'])->name('password.request');
    Route::get('/reset-password/{token}', [AuthPageController::class, 'resetPassword'])->name('password.reset');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/favoritos', [ListingFavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favoritos/{listing:slug}', [ListingFavoriteController::class, 'store'])->name('favorites.store');
    Route::delete('/favoritos/{listing:slug}', [ListingFavoriteController::class, 'destroy'])->name('favorites.destroy');
    Route::resource('/admin/anuncios', ListingController::class)
        ->parameters(['anuncios' => 'listing'])
        ->names('admin.listings')
        ->except('show');
    Route::post('/media', [MediaAssetController::class, 'store'])->name('media.store');
    Route::delete('/media/{mediaAsset}', [MediaAssetController::class, 'destroy'])->name('media.destroy');
});
