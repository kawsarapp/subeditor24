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
            if (!Schema::hasColumn('user_settings', 'tts_enabled')) {
                $table->boolean('tts_enabled')->default(false)->after('custom_api_mapping');
            }
            if (!Schema::hasColumn('user_settings', 'tts_provider')) {
                $table->string('tts_provider', 50)->default('edgetts')->after('tts_enabled');
            }
            if (!Schema::hasColumn('user_settings', 'tts_voice_male')) {
                $table->string('tts_voice_male', 100)->nullable()->after('tts_provider');
            }
            if (!Schema::hasColumn('user_settings', 'tts_voice_female')) {
                $table->string('tts_voice_female', 100)->nullable()->after('tts_voice_male');
            }
            if (!Schema::hasColumn('user_settings', 'tts_selected_gender')) {
                $table->string('tts_selected_gender', 20)->default('male')->after('tts_voice_female');
            }
            if (!Schema::hasColumn('user_settings', 'tts_speed')) {
                $table->decimal('tts_speed', 3, 2)->default(1.00)->after('tts_selected_gender');
            }
            if (!Schema::hasColumn('user_settings', 'tts_embed_mode')) {
                $table->string('tts_embed_mode', 30)->default('top')->after('tts_speed'); // 'top', 'bottom', 'api_only', 'none'
            }
            if (!Schema::hasColumn('user_settings', 'tts_openai_key')) {
                $table->string('tts_openai_key', 255)->nullable()->after('tts_embed_mode');
            }
            if (!Schema::hasColumn('user_settings', 'tts_elevenlabs_key')) {
                $table->string('tts_elevenlabs_key', 255)->nullable()->after('tts_openai_key');
            }
            if (!Schema::hasColumn('user_settings', 'tts_google_key')) {
                $table->text('tts_google_key')->nullable()->after('tts_elevenlabs_key');
            }
            if (!Schema::hasColumn('user_settings', 'tts_auto_generate_on_draft')) {
                $table->boolean('tts_auto_generate_on_draft')->default(true)->after('tts_google_key');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropColumn([
                'tts_enabled', 'tts_provider', 'tts_voice_male', 'tts_voice_female',
                'tts_selected_gender', 'tts_speed', 'tts_embed_mode', 'tts_openai_key',
                'tts_elevenlabs_key', 'tts_google_key', 'tts_auto_generate_on_draft'
            ]);
        });
    }
};
