<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if (blank($user->slug)) {
                $user->slug = static::uniqueSlug($user->name);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    private static function uniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name) ?: 'anunciante';

        if (ctype_digit($baseSlug)) {
            $baseSlug = "anunciante-{$baseSlug}";
        }

        $slug = $baseSlug;
        $suffix = 2;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
