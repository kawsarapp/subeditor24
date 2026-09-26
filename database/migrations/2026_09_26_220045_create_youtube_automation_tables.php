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
        Schema::create('youtube_channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('channel_id')->index();
            $table->string('channel_title');
            $table->text('channel_description')->nullable();
            $table->string('custom_url')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->unsignedBigInteger('subscriber_count')->default(0);
            $table->unsignedInteger('video_count')->default(0);
            $table->unsignedBigInteger('view_count')->default(0);
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->boolean('auto_pilot_enabled')->default(false);
            $table->string('default_language')->default('bn'); // 'bn', 'en', 'hi', etc.
            $table->string('default_privacy')->default('public'); // 'public', 'unlisted', 'private'
            $table->string('title_style')->default('viral_curiosity'); // 'viral_curiosity', 'breaking_news', 'seo_keyword', 'storytelling'
            $table->text('custom_tags_template')->nullable();
            $table->text('custom_description_footer')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'channel_id']);
        });

        Schema::create('youtube_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('youtube_channel_id')->constrained('youtube_channels')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('video_id')->index();
            $table->text('original_title');
            $table->longText('original_description')->nullable();
            $table->json('original_tags')->nullable();
            $table->string('original_privacy_status')->default('unlisted'); // 'unlisted', 'private', 'public'
            $table->string('current_privacy_status')->default('unlisted');
            $table->text('thumbnail_url')->nullable();
            $table->string('duration')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('youtube_category_id')->nullable();
            
            // AI SEO Generated Fields
            $table->text('ai_title')->nullable();
            $table->json('ai_title_variations')->nullable();
            $table->longText('ai_description')->nullable();
            $table->json('ai_tags')->nullable();
            $table->json('ai_hashtags')->nullable();
            $table->json('ai_chapters')->nullable();
            $table->unsignedSmallInteger('seo_score')->default(0);
            
            // Automation Pipeline State
            $table->enum('status', ['synced', 'optimizing', 'optimized', 'publishing', 'published', 'failed'])->default('synced')->index();
            $table->text('error_message')->nullable();
            $table->timestamp('optimized_at')->nullable();
            $table->timestamp('last_published_at')->nullable();
            $table->timestamps();

            $table->unique(['youtube_channel_id', 'video_id']);
        });

        Schema::create('youtube_automation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('youtube_channel_id')->nullable()->constrained('youtube_channels')->onDelete('cascade');
            $table->foreignId('youtube_video_id')->nullable()->constrained('youtube_videos')->onDelete('cascade');
            $table->string('action'); // 'auth', 'sync', 'ai_seo', 'publish', 'token_refresh', 'error'
            $table->string('status')->default('info'); // 'success', 'failed', 'info', 'warning'
            $table->text('message');
            $table->json('details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('youtube_automation_logs');
        Schema::dropIfExists('youtube_videos');
        Schema::dropIfExists('youtube_channels');
    }
};
