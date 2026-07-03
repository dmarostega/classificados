<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('media_asset_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_cover')->default(false);
            $table->timestamps();

            $table->unique(['listing_id', 'media_asset_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_images');
    }
};
