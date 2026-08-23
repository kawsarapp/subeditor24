<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Instant Indexing logs & API tracking.
     */
    public function up(): void
    {
        Schema::create('seo_instant_indexing_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seo_website_id')->constrained('seo_websites')->onDelete('cascade');
            $table->string('url', 2048);
            $table->string('engine')->default('both'); // google, bing_indexnow, both
            $table->string('api_status')->default('working'); // working, connection_error, quota_exceeded
            $table->integer('response_code')->default(200);
            $table->string('indexing_status')->default('indexed'); // indexed, pending, failed
            $table->text('notes')->nullable();
            $table->timestamp('pushed_at')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_instant_indexing_logs');
    }
};
