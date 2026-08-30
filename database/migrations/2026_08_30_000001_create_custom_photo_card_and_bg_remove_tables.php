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
        if (!Schema::hasColumn('users', 'daily_bg_remove_limit')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('daily_bg_remove_limit')->default(20)->nullable()->after('daily_post_limit');
            });
        }

        if (!Schema::hasColumn('user_settings', 'bg_remove_credit_cost')) {
            Schema::table('user_settings', function (Blueprint $table) {
                $table->integer('bg_remove_credit_cost')->default(1)->nullable()->after('scrape_concurrent_limit');
            });
        }

        if (!Schema::hasTable('bg_remove_logs')) {
            Schema::create('bg_remove_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->text('original_image_url')->nullable();
                $table->text('output_image_url')->nullable();
                $table->integer('credits_deducted')->default(1);
                $table->string('status', 50)->default('success');
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bg_remove_logs');

        if (Schema::hasColumn('users', 'daily_bg_remove_limit')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('daily_bg_remove_limit');
            });
        }

        if (Schema::hasColumn('user_settings', 'bg_remove_credit_cost')) {
            Schema::table('user_settings', function (Blueprint $table) {
                $table->dropColumn('bg_remove_credit_cost');
            });
        }
    }
};
