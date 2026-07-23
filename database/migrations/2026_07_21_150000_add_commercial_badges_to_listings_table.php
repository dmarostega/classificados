<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table): void {
            $table->boolean('accepts_offers')->default(false)->after('price_cents');
            $table->boolean('quick_sale')->default(false)->after('accepts_offers');
            $table->boolean('negotiable_price')->default(false)->after('quick_sale');
            $table->boolean('easy_pickup')->default(false)->after('negotiable_price');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table): void {
            $table->dropColumn([
                'accepts_offers',
                'quick_sale',
                'negotiable_price',
                'easy_pickup',
            ]);
        });
    }
};
