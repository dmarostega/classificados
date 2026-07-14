<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listing_favorites', function (Blueprint $table): void {
            $table->boolean('email_notifications_enabled')->default(true)->after('listing_id');
            $table->timestamp('last_notification_sent_at')->nullable()->after('email_notifications_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('listing_favorites', function (Blueprint $table): void {
            $table->dropColumn(['email_notifications_enabled', 'last_notification_sent_at']);
        });
    }
};
