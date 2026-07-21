<?php

namespace App\Models;

use App\Enums\ListingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Listing extends Model
{
    private const COMMERCIAL_BADGES = [
        'accepts_offers' => 'Aceita proposta',
        'quick_sale' => 'Venda rápida',
        'negotiable_price' => 'Preço negociável',
        'easy_pickup' => 'Retirada facilitada',
    ];

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'description',
        'price_cents',
        'accepts_offers',
        'quick_sale',
        'negotiable_price',
        'easy_pickup',
        'city',
        'state',
        'contact_name',
        'contact_email',
        'contact_phone',
        'status',
        'published_at',
        'expires_at',
        'views_count',
    ];

    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
            'accepts_offers' => 'boolean',
            'quick_sale' => 'boolean',
            'negotiable_price' => 'boolean',
            'easy_pickup' => 'boolean',
            'status' => ListingStatus::class,
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'views_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ListingImage::class)->orderBy('sort_order');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(ListingFavorite::class);
    }

    public function coverImage(): HasMany
    {
        return $this->images()->where('is_cover', true);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query
            ->where('status', ListingStatus::Published)
            ->whereNotNull('published_at')
            ->where(function (Builder $query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', Carbon::now());
            });
    }

    public function publicUrl(): string
    {
        return route('listings.show', $this->slug);
    }

    public function isPubliclyVisible(): bool
    {
        return $this->status->isPublic()
            && $this->published_at !== null
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function formattedPrice(): string
    {
        return 'R$ '.number_format($this->price_cents / 100, 2, ',', '.');
    }

    public function commercialBadges(): array
    {
        return collect(self::COMMERCIAL_BADGES)
            ->filter(fn (string $label, string $attribute): bool => (bool) $this->{$attribute})
            ->values()
            ->all();
    }

    public function maskedContactPhone(): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $this->contact_phone);

        if ($digits === '') {
            return null;
        }

        if (strlen($digits) === 11) {
            return sprintf('(%s) %s****-%s', substr($digits, 0, 2), $digits[2], substr($digits, -4));
        }

        if (strlen($digits) === 10) {
            return sprintf('(%s) ****-%s', substr($digits, 0, 2), substr($digits, -4));
        }

        return str_repeat('*', max(strlen($digits) - 4, 0)).substr($digits, -4);
    }
}
