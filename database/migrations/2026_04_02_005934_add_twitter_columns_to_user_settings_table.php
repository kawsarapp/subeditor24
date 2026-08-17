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
            $table->text('twitter_api_key')->nullable()->after('telegram_bot_token');
            $table->text('twitter_api_secret')->nullable()->after('twitter_api_key');
            $table->text('twitter_access_token')->nullable()->after('twitter_api_secret');
            $table->text('twitter_access_secret')->nullable()->after('twitter_access_token');
            $table->boolean('post_to_twitter')->default(false)->after('twitter_access_secret');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropColumn([
                'twitter_api_key',
                'twitter_api_secret',
                'twitter_access_token',
                'twitter_access_secret',
                'post_to_twitter'
            ]);
        });
    }
};
