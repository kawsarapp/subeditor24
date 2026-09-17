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
        Schema::create('central_news_pool', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('website_id')->nullable()->index();
            $table->string('title', 500);
            $table->string('slug_hash', 64)->unique()->comment('SHA-256 hash of URL for instant O(1) dedup');
            $table->text('original_link');
            $table->text('thumbnail_url')->nullable();
            $table->longText('content')->nullable();
            $table->string('source_name', 150)->nullable()->index();
            $table->string('source_domain', 150)->nullable()->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();

            // Composite indexes for fast timeline queries and auto-cleanup
            $table->index(['website_id', 'created_at']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('central_news_pool');
    }
};
