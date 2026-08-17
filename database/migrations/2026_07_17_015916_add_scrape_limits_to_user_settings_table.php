<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->integer('scrape_cooldown_minutes')->default(5)->after('scraper_method');
            $table->integer('scrape_concurrent_limit')->default(3)->after('scrape_cooldown_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropColumn(['scrape_cooldown_minutes', 'scrape_concurrent_limit']);
        });
    }
};
