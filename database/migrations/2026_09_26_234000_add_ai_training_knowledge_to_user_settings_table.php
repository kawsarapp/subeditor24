<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('user_settings', 'ai_copilot_custom_knowledge')) {
                $table->longText('ai_copilot_custom_knowledge')->nullable()->after('custom_rewrite_prompt')
                    ->comment('Custom editorial rules, persona, and training knowledge base for AI');
            }
            if (!Schema::hasColumn('user_settings', 'ai_copilot_few_shot_examples')) {
                $table->longText('ai_copilot_few_shot_examples')->nullable()->after('ai_copilot_custom_knowledge')
                    ->comment('Few-shot input and output examples to train the AI with zero hallucination');
            }
            if (!Schema::hasColumn('user_settings', 'ai_copilot_temperature')) {
                $table->decimal('ai_copilot_temperature', 3, 2)->default(0.30)->after('ai_copilot_few_shot_examples')
                    ->comment('AI Temperature / Strictness Control (0.1 to 1.0)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropColumn([
                'ai_copilot_custom_knowledge',
                'ai_copilot_few_shot_examples',
                'ai_copilot_temperature',
            ]);
        });
    }
};
