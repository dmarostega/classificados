<?php

use App\Enums\ListingStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('description');
            $table->unsignedBigInteger('price_cents');
            $table->string('city');
            $table->string('state', 2);
            $table->string('contact_name');
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('status')->default(ListingStatus::Draft->value);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'slug']);
            $table->index(['status', 'published_at']);
            $table->index(['category_id', 'city', 'state']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
