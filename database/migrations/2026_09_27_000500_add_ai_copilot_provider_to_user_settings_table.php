<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('user_settings', 'ai_copilot_provider')) {
                $table->string('ai_copilot_provider', 30)->nullable()->default('default')->after('ai_copilot_temperature')
                    ->comment('Specific AI engine chosen for AI Copilot chat assistant (default, deepseek, openai, gemini, groq, huggingface)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropColumn('ai_copilot_provider');
        });
    }
};
