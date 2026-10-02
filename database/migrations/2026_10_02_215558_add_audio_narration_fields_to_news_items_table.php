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
            if (!Schema::hasColumn('news_items', 'audio_url')) {
                $table->string('audio_url', 500)->nullable()->after('thumbnail_url');
            }
            if (!Schema::hasColumn('news_items', 'audio_path')) {
                $table->string('audio_path', 500)->nullable()->after('audio_url');
            }
            if (!Schema::hasColumn('news_items', 'audio_provider')) {
                $table->string('audio_provider', 50)->nullable()->after('audio_path');
            }
            if (!Schema::hasColumn('news_items', 'audio_voice')) {
                $table->string('audio_voice', 100)->nullable()->after('audio_provider');
            }
            if (!Schema::hasColumn('news_items', 'audio_duration')) {
                $table->integer('audio_duration')->nullable()->after('audio_voice');
            }
            if (!Schema::hasColumn('news_items', 'audio_status')) {
                $table->string('audio_status', 30)->default('disabled')->after('audio_duration');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news_items', function (Blueprint $table) {
            $table->dropColumn(['audio_url', 'audio_path', 'audio_provider', 'audio_voice', 'audio_duration', 'audio_status']);
        });
    }
};
