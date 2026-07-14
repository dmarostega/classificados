<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('slug')->nullable()->after('name');
        });

        $usedSlugs = [];

        DB::table('users')->orderBy('id')->get(['id', 'name'])->each(function (object $user) use (&$usedSlugs): void {
            $baseSlug = Str::slug($user->name) ?: 'anunciante';

            if (ctype_digit($baseSlug)) {
                $baseSlug = "anunciante-{$baseSlug}";
            }

            $slug = $baseSlug;
            $suffix = 2;

            while (isset($usedSlugs[$slug])) {
                $slug = "{$baseSlug}-{$suffix}";
                $suffix++;
            }

            DB::table('users')->where('id', $user->id)->update(['slug' => $slug]);
            $usedSlugs[$slug] = true;
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
