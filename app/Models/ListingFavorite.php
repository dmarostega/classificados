<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingFavorite extends Model
{
    protected $fillable = [
        'user_id',
        'listing_id',
        'email_notifications_enabled',
        'last_notification_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'email_notifications_enabled' => 'boolean',
            'last_notification_sent_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }
}
