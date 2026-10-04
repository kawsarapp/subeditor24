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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'post_limit_type')) {
                $table->string('post_limit_type')->default('daily')->after('daily_post_limit');
            }
            if (!Schema::hasColumn('users', 'monthly_post_limit')) {
                $table->integer('monthly_post_limit')->nullable()->after('post_limit_type');
            }
        });

        Schema::table('pricing_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('pricing_plans', 'post_limit_type')) {
                $table->string('post_limit_type')->default('daily')->after('daily_news_limit');
            }
            if (!Schema::hasColumn('pricing_plans', 'monthly_post_limit')) {
                $table->integer('monthly_post_limit')->nullable()->after('post_limit_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['post_limit_type', 'monthly_post_limit']);
        });

        Schema::table('pricing_plans', function (Blueprint $table) {
            $table->dropColumn(['post_limit_type', 'monthly_post_limit']);
        });
    }
};
