<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_items', function (Blueprint $table) {
            // Composite index for drafts and published status queries
            $table->index(['user_id', 'status', 'updated_at'], 'idx_news_user_status_updated');
            // Composite index for staff & admin visibility queries
            $table->index(['staff_id', 'user_id'], 'idx_news_staff_user');
            // Composite index for filtering rewritten drafts
            $table->index(['user_id', 'is_rewritten', 'status'], 'idx_news_user_rewritten_status');
        });
    }

    public function down(): void
    {
        Schema::table('news_items', function (Blueprint $table) {
            $table->dropIndex('idx_news_user_status_updated');
            $table->dropIndex('idx_news_staff_user');
            $table->dropIndex('idx_news_user_rewritten_status');
        });
    }
};
