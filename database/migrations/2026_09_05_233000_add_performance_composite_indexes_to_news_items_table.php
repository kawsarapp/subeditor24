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
        Schema::table('news_items', function (Blueprint $table) {
            $table->index(['user_id', 'is_rewritten', 'status'], 'idx_news_feed_filter');
            $table->index(['user_id', 'is_posted', 'created_at'], 'idx_news_user_posted_created');
            $table->index(['user_id', 'is_posted', 'scheduled_at'], 'idx_news_user_scheduled');
            $table->index(['user_id', 'status', 'created_at'], 'idx_news_user_status_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news_items', function (Blueprint $table) {
            $table->dropIndex('idx_news_feed_filter');
            $table->dropIndex('idx_news_user_posted_created');
            $table->dropIndex('idx_news_user_scheduled');
            $table->dropIndex('idx_news_user_status_created');
        });
    }
};
