<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for isolated SEO Intelligence SaaS module.
     */
    public function up(): void
    {
        // 1. SEO Websites Table
        Schema::create('seo_websites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('domain');
            $table->string('target_url');
            $table->string('verification_txt_token')->nullable();
            $table->boolean('is_verified')->default(false);
            
            // Encrypted Google OAuth Credentials & Accounts
            $table->text('google_access_token')->nullable();
            $table->text('google_refresh_token')->nullable();
            $table->timestamp('google_token_expires_at')->nullable();
            $table->string('gsc_property_id')->nullable();
            $table->string('ga4_property_id')->nullable();
            
            // Auto Detected Meta & Health
            $table->string('cms_detected')->nullable();
            $table->string('sitemap_url')->nullable();
            $table->string('robots_txt_url')->nullable();
            $table->integer('seo_health_score')->default(0);
            
            $table->timestamps();
        });

        // 2. SEO Page Audits Table
        Schema::create('seo_page_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seo_website_id')->constrained('seo_websites')->onDelete('cascade');
            $table->string('url', 2048);
            $table->string('url_hash', 64)->index();
            $table->integer('status_code')->default(200);

            $table->string('title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('h1_tag')->nullable();
            $table->string('canonical_url')->nullable();
            $table->integer('word_count')->default(0);
            $table->float('load_time_ms')->default(0);
            $table->boolean('is_indexed')->default(true);

            // Audit Issues (JSON)
            $table->json('issues_found')->nullable(); // ['missing_title', 'missing_meta', 'broken_links', etc.]
            $table->json('schema_detected')->nullable();

            $table->timestamp('crawled_at')->nullable();
            $table->timestamps();
        });

        // 3. SEO Keyword Metrics Table (GSC Intelligence)
        Schema::create('seo_keyword_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seo_website_id')->constrained('seo_websites')->onDelete('cascade');
            $table->string('keyword')->index();
            $table->string('target_page_url', 2048)->nullable();
            
            $table->integer('clicks')->default(0);
            $table->integer('impressions')->default(0);
            $table->float('ctr')->default(0);
            $table->float('avg_position')->default(0);
            
            $table->boolean('is_quick_win')->default(false); // Position 4 - 15
            $table->string('trend_status')->default('stable'); // winning, losing, new, declining

            $table->date('metric_date')->index();
            $table->timestamps();
        });

        // 4. Core Web Vitals Table
        Schema::create('seo_core_web_vitals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seo_website_id')->constrained('seo_websites')->onDelete('cascade');
            $table->string('url', 2048);
            
            $table->float('lcp_sec')->default(0); // Largest Contentful Paint
            $table->float('inp_ms')->default(0);  // Interaction to Next Paint
            $table->float('cls_score')->default(0); // Cumulative Layout Shift
            $table->float('fcp_sec')->default(0); // First Contentful Paint
            $table->float('ttfb_ms')->default(0); // Time to First Byte

            $table->string('overall_rating')->default('Good'); // Good, Needs Improvement, Poor

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_core_web_vitals');
        Schema::dropIfExists('seo_keyword_metrics');
        Schema::dropIfExists('seo_page_audits');
        Schema::dropIfExists('seo_websites');
    }
};
