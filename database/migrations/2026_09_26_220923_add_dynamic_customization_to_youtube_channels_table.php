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
            $table->boolean('opt_title')->default(true)->after('title_style');
            $table->boolean('opt_description')->default(true)->after('opt_title');
            $table->boolean('opt_tags')->default(true)->after('opt_description');
            $table->boolean('opt_hashtags')->default(true)->after('opt_tags');
            $table->boolean('opt_chapters')->default(true)->after('opt_hashtags');
            $table->boolean('append_footer')->default(true)->after('opt_chapters');
            $table->boolean('merge_brand_tags')->default(true)->after('append_footer');
            $table->text('custom_ai_prompt')->nullable()->after('merge_brand_tags');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('youtube_channels', function (Blueprint $table) {
            $table->dropColumn([
                'opt_title',
                'opt_description',
                'opt_tags',
                'opt_hashtags',
                'opt_chapters',
                'append_footer',
                'merge_brand_tags',
                'custom_ai_prompt',
            ]);
        });
    }
};
