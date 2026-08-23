<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to add SaaS daily limits to users table.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'daily_crawl_limit')) {
                $table->integer('daily_crawl_limit')->default(5)->after('daily_post_limit');
            }
            if (!Schema::hasColumn('users', 'daily_ai_limit')) {
                $table->integer('daily_ai_limit')->default(25)->after('daily_crawl_limit');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['daily_crawl_limit', 'daily_ai_limit']);
        });
    }
};
