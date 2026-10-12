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
        Schema::table('seo_websites', function (Blueprint $table) {
            $table->string('crawl_status')->default('idle')->after('seo_health_score');
            $table->integer('crawl_progress')->default(0)->after('crawl_status');
            $table->timestamp('last_crawled_at')->nullable()->after('crawl_progress');
            $table->text('crawl_error')->nullable()->after('last_crawled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seo_websites', function (Blueprint $table) {
            $table->dropColumn(['crawl_status', 'crawl_progress', 'last_crawled_at', 'crawl_error']);
        });
    }
};
