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
        Schema::table('youtube_channels', function (Blueprint $table) {
            $table->boolean('opt_thumbnail_ideas')->default(true)->after('opt_chapters');
            $table->boolean('opt_pinned_comment')->default(true)->after('opt_thumbnail_ideas');
            $table->boolean('opt_dual_language')->default(true)->after('opt_pinned_comment');
        });

        Schema::table('youtube_videos', function (Blueprint $table) {
            $table->json('ai_thumbnail_ideas')->nullable()->after('ai_title_variations');
            $table->text('ai_pinned_comment')->nullable()->after('ai_chapters');
            $table->json('ai_search_intent_keywords')->nullable()->after('ai_pinned_comment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('youtube_channels', function (Blueprint $table) {
            $table->dropColumn(['opt_thumbnail_ideas', 'opt_pinned_comment', 'opt_dual_language']);
        });

        Schema::table('youtube_videos', function (Blueprint $table) {
            $table->dropColumn(['ai_thumbnail_ideas', 'ai_pinned_comment', 'ai_search_intent_keywords']);
        });
    }
};
