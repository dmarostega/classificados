<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('featured_listing_id')->nullable()->after('remember_token')->constrained('listings')->nullOnDelete();
            $table->foreignUlid('og_media_asset_id')->nullable()->after('featured_listing_id')->constrained('media_assets')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('og_media_asset_id');
            $table->dropConstrainedForeignId('featured_listing_id');
        });
    }
};
