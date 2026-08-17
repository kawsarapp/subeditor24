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
            $table->integer('plagiarism_score')->nullable()->after('hashtags');
            $table->string('fact_check_status')->nullable()->after('plagiarism_score');
            $table->text('fact_check_report')->nullable()->after('fact_check_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news_items', function (Blueprint $table) {
            $table->dropColumn(['plagiarism_score', 'fact_check_status', 'fact_check_report']);
        });
    }
};
