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
        if (Schema::hasTable('user_settings') && !Schema::hasColumn('user_settings', 'pricing_page_config')) {
            Schema::table('user_settings', function (Blueprint $table) {
                $table->json('pricing_page_config')->nullable()->after('design_preferences');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user_settings') && Schema::hasColumn('user_settings', 'pricing_page_config')) {
            Schema::table('user_settings', function (Blueprint $table) {
                $table->dropColumn('pricing_page_config');
            });
        }
    }
};
