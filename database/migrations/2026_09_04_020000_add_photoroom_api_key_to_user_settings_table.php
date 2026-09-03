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
            if (!Schema::hasColumn('user_settings', 'photoroom_api_key')) {
                $table->text('photoroom_api_key')->nullable()->after('smartproxy_api_token');
            } else {
                $table->text('photoroom_api_key')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            if (Schema::hasColumn('user_settings', 'photoroom_api_key')) {
                $table->dropColumn('photoroom_api_key');
            }
        });
    }
};
