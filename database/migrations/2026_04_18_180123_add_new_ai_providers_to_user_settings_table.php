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
            $table->string('primary_ai')->default('deepseek')->after('deepseek_model');
            $table->text('groq_api_key')->nullable()->after('primary_ai');
            $table->string('groq_model')->nullable()->after('groq_api_key');
            $table->text('qwen_api_key')->nullable()->after('groq_model');
            $table->string('qwen_model')->nullable()->after('qwen_api_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropColumn([
                'primary_ai',
                'groq_api_key',
                'groq_model',
                'qwen_api_key',
                'qwen_model'
            ]);
        });
    }
};
