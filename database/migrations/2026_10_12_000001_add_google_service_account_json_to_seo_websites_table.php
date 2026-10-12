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
        Schema::table('seo_websites', function (Blueprint $table) {
            $table->longText('google_service_account_json')->nullable()->after('google_token_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seo_websites', function (Blueprint $table) {
            $table->dropColumn('google_service_account_json');
        });
    }
};
