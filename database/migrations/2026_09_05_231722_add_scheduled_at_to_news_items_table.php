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
            if (!Schema::hasColumn('news_items', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable()->after('is_queued')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news_items', function (Blueprint $table) {
            if (Schema::hasColumn('news_items', 'scheduled_at')) {
                $table->dropColumn('scheduled_at');
            }
        });
    }
};
