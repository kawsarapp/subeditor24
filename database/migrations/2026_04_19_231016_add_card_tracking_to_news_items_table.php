<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_items', function (Blueprint $table) {
            if (!Schema::hasColumn('news_items', 'card_created_at')) {
                $table->timestamp('card_created_at')->nullable()->after('hashtags');
            }
            if (!Schema::hasColumn('news_items', 'card_download_count')) {
                $table->unsignedSmallInteger('card_download_count')->default(0)->after('card_created_at');
            }
            
            // Adding an index to speed up analytics queries
            $table->index(['user_id', 'card_created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('news_items', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'card_created_at']);
            $table->dropColumn(['card_created_at', 'card_download_count']);
        });
    }
};
