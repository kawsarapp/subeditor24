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
            if (!Schema::hasColumn('user_settings', 'fb_app_id')) {
                $table->string('fb_app_id')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('user_settings', 'fb_app_secret')) {
                $table->text('fb_app_secret')->nullable()->after('fb_app_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            if (Schema::hasColumn('user_settings', 'fb_app_id')) {
                $table->dropColumn('fb_app_id');
            }
            if (Schema::hasColumn('user_settings', 'fb_app_secret')) {
                $table->dropColumn('fb_app_secret');
            }
        });
    }
};
