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
            if (!Schema::hasColumn('user_settings', 'scraping_api_provider')) {
                $table->string('scraping_api_provider', 50)->default('scrape_do')->after('smartproxy_api_token');
            }
            if (!Schema::hasColumn('user_settings', 'scrape_do_token')) {
                $table->text('scrape_do_token')->nullable()->after('scraping_api_provider');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropColumn(['scraping_api_provider', 'scrape_do_token']);
        });
    }
};
