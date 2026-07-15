<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $usedSlugs = [];

        DB::table('listings')->orderBy('id')->get(['id', 'slug'])->each(function (object $listing) use (&$usedSlugs): void {
            $baseSlug = ctype_digit($listing->slug) ? "anuncio-{$listing->slug}" : $listing->slug;
            $slug = $baseSlug;
            $suffix = 2;

            while (isset($usedSlugs[$slug])) {
                $slug = "{$baseSlug}-{$suffix}";
                $suffix++;
            }

            if ($slug !== $listing->slug) {
                DB::table('listings')->where('id', $listing->id)->update(['slug' => $slug]);
            }

            $usedSlugs[$slug] = true;
        });

        Schema::table('listings', function (Blueprint $table): void {
            $table->dropUnique(['user_id', 'slug']);
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->unique(['user_id', 'slug']);
        });
    }
};
