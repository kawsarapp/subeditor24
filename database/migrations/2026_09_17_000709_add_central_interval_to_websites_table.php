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
        Schema::table('websites', function (Blueprint $table) {
            if (!Schema::hasColumn('websites', 'scrape_interval_minutes')) {
                $table->unsignedSmallInteger('scrape_interval_minutes')->default(5)->after('last_scraped_at');
            }
            if (!Schema::hasColumn('websites', 'is_central_active')) {
                $table->boolean('is_central_active')->default(true)->after('scrape_interval_minutes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->dropColumn(['scrape_interval_minutes', 'is_central_active']);
        });
    }
};
